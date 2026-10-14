<?php

namespace Tests\Feature\Services;

use App\Enums\CompetitionStatus;
use App\Enums\CompetitionType;
use App\Enums\ExtensionType;
use App\Enums\RewardMode;
use App\Enums\ScoringType;
use App\Enums\TrackStatus;
use App\Enums\TransportMode;
use App\Enums\UserType;
use App\Jobs\ProcessTrackJob;
use App\Models\Competition;
use App\Models\Track;
use App\Models\TrackSegment;
use App\Models\User;
use App\Services\BadgeService;
use App\Services\TrackValidationManager;
use App\Services\TrackValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TrackValidationManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Competition $competition;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->mock(BadgeService::class, function ($mock) {
            $mock->shouldReceive('checkAndAward')->andReturn(collect());
        });

        $ente = User::factory()->create(['type' => UserType::ENTE]);
        $this->user = User::factory()->create(['credits' => 0]);

        $this->competition = Competition::create([
            'name' => 'Gara validazione manuale',
            'ente_id' => $ente->id,
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(30),
            'status' => CompetitionStatus::ACTIVE,
            'is_public' => true,
            'extension_type' => ExtensionType::NATIONAL,
            'competition_type' => CompetitionType::NATIONAL,
            'reward_mode' => RewardMode::CREDITS_BASED,
            'scoring_type' => ScoringType::DISTANCE,
            'credits_per_km' => 2.0,
            'credits_multiplier' => 1.0,
            'credits_to_euro' => 0,
            'max_daily_tracks' => 99,
        ]);
        $this->competition->users()->attach($this->user->id, [
            'status' => 'approved',
            'registered_at' => now(),
            'approved_at' => now(),
        ]);
    }

    private function createTrack(): Track
    {
        return Track::create([
            'user_id' => $this->user->id,
            'competition_id' => $this->competition->id,
            'session_id' => 'session-' . uniqid(),
            'status' => TrackStatus::PENDING,
            'started_at' => now(),
            'ended_at' => now()->addMinutes(30),
            'duration_seconds' => 1800,
        ]);
    }

    private function createSegment(Track $track, TransportMode $mode, int $meters, ?float $maxChunkSpeedKmh = null): TrackSegment
    {
        return TrackSegment::create([
            'track_id' => $track->id,
            'sequence' => $track->segments()->count(),
            'transport_mode' => $mode,
            'status' => 'pending',
            'distance_meters' => $meters,
            'duration_seconds' => 600,
            'points_count' => 50,
            'max_chunk_speed_kmh' => $maxChunkSpeedKmh,
            'generates_credits' => true,
        ]);
    }

    public function test_manual_validation_credits_a_rejected_track_with_competition_rules(): void
    {
        $track = $this->createTrack();
        // 1 km a piedi a 25 km/h: scartato dal controllo velocità
        $this->createSegment($track, TransportMode::WALK, 1000, 25.0);
        app(TrackValidationService::class)->validate($track->fresh());
        $this->assertSame(TrackStatus::INVALID, $track->fresh()->status);

        $delta = app(TrackValidationManager::class)->validate($track->fresh());

        $track->refresh();
        $this->assertSame(TrackStatus::VALID, $track->status);
        $this->assertEqualsWithDelta(2.0, $delta, 0.0001); // 1 km x 2 crediti/km
        $this->assertEqualsWithDelta(2.0, (float) $track->credits_earned, 0.0001);
        $this->assertEqualsWithDelta(2.0, (float) $this->user->fresh()->credits, 0.0001);
        $this->assertSame('valid', $track->segments->first()->status);
    }

    public function test_manual_validation_never_credits_car_km(): void
    {
        $track = $this->createTrack();
        $this->createSegment($track, TransportMode::WALK, 1000, 25.0);
        $this->createSegment($track, TransportMode::CAR, 5000);
        app(TrackValidationService::class)->validate($track->fresh());

        app(TrackValidationManager::class)->validate($track->fresh());

        $car = $track->fresh()->segments->firstWhere('transport_mode', TransportMode::CAR);
        $this->assertSame('not_credited', $car->status);
        $this->assertEquals(0, (float) $car->credits_earned);
        $this->assertEqualsWithDelta(2.0, (float) $this->user->fresh()->credits, 0.0001);
    }

    public function test_manual_validation_of_partially_valid_track_adds_only_the_difference(): void
    {
        $track = $this->createTrack();
        $this->createSegment($track, TransportMode::WALK, 1000, 6.0);
        $this->createSegment($track, TransportMode::WALK, 1000, 25.0);
        app(TrackValidationService::class)->validate($track->fresh());
        $this->assertEqualsWithDelta(2.0, (float) $this->user->fresh()->credits, 0.0001);

        $delta = app(TrackValidationManager::class)->validate($track->fresh());

        $this->assertEqualsWithDelta(2.0, $delta, 0.0001);
        $this->assertEqualsWithDelta(4.0, (float) $track->fresh()->credits_earned, 0.0001);
        $this->assertEqualsWithDelta(4.0, (float) $this->user->fresh()->credits, 0.0001);
    }

    public function test_queued_processing_skips_a_track_validated_manually(): void
    {
        $track = $this->createTrack();
        $this->createSegment($track, TransportMode::WALK, 1000, 6.0);

        app(TrackValidationManager::class)->validate($track->fresh());
        (new ProcessTrackJob($track->fresh()))->handle(app(TrackValidationService::class));

        $this->assertEqualsWithDelta(2.0, (float) $this->user->fresh()->credits, 0.0001);
    }

    public function test_manual_validation_does_not_credit_modes_excluded_by_the_competition(): void
    {
        $this->competition->update(['allowed_transport_modes' => ['walk']]);
        $track = $this->createTrack();
        $this->createSegment($track, TransportMode::WALK, 1000, 25.0); // scartato per velocità
        $this->createSegment($track, TransportMode::BIKE, 3000, 20.0); // bici non ammessa dalla gara
        app(TrackValidationService::class)->validate($track->fresh());

        app(TrackValidationManager::class)->validate($track->fresh());

        $bike = $track->fresh()->segments->firstWhere('transport_mode', TransportMode::BIKE);
        $this->assertSame('not_credited', $bike->status);
        $this->assertEquals(0, (float) $bike->credits_earned);
        // Solo il km a piedi (errore di rilevazione superato) genera crediti
        $this->assertEqualsWithDelta(2.0, (float) $this->user->fresh()->credits, 0.0001);
    }
}
