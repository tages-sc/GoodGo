<?php

namespace Tests\Feature;

use App\Enums\CompetitionStatus;
use App\Enums\CompetitionType;
use App\Enums\ExtensionType;
use App\Enums\RewardMode;
use App\Enums\ScoringType;
use App\Enums\TrackStatus;
use App\Enums\TransportMode;
use App\Enums\UserType;
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

class CompetitionLeaderboardTest extends TestCase
{
    use RefreshDatabase;

    private Competition $competition;
    private User $alice;
    private User $bob;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->mock(BadgeService::class, function ($mock) {
            $mock->shouldReceive('checkAndAward')->andReturn(collect());
        });

        $ente = User::factory()->create(['type' => UserType::ENTE]);
        $this->alice = User::factory()->create();
        $this->bob = User::factory()->create();

        $this->competition = Competition::create([
            'name' => 'Gara classifica',
            'ente_id' => $ente->id,
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(30),
            'status' => CompetitionStatus::ACTIVE,
            'is_public' => true,
            'extension_type' => ExtensionType::NATIONAL,
            'competition_type' => CompetitionType::NATIONAL,
            'reward_mode' => RewardMode::CREDITS_BASED,
            'scoring_type' => ScoringType::DISTANCE,
            'leaderboard_type' => 'km',
            'credits_per_km' => 1.0,
            'credits_multiplier' => 1.0,
            'credits_to_euro' => 0,
            'max_daily_tracks' => 99,
        ]);

        foreach ([$this->alice, $this->bob] as $user) {
            $this->competition->users()->attach($user->id, [
                'status' => 'approved',
                'registered_at' => now(),
                'approved_at' => now(),
            ]);
        }
    }

    /**
     * Traccia con un segmento a piedi accreditato lungo valid_distance_meters.
     */
    private function createTrack(User $user, array $overrides = []): Track
    {
        $track = Track::create(array_merge([
            'user_id' => $user->id,
            'competition_id' => $this->competition->id,
            'session_id' => 'session-' . uniqid(),
            'status' => TrackStatus::PENDING,
            'started_at' => now(),
            'ended_at' => now()->addMinutes(30),
            'duration_seconds' => 1800,
        ], $overrides));

        if ($track->valid_distance_meters > 0) {
            $this->createSegment($track, TransportMode::WALK, (int) $track->valid_distance_meters);
        }

        return $track;
    }

    private function createSegment(Track $track, TransportMode $mode, int $meters, string $status = 'valid'): TrackSegment
    {
        return TrackSegment::create([
            'track_id' => $track->id,
            'sequence' => $track->segments()->count(),
            'transport_mode' => $mode,
            'status' => $status,
            'generates_credits' => $status === 'valid' && $mode->generatesCredits(),
            'distance_meters' => $meters,
            'duration_seconds' => 600,
        ]);
    }

    private function pivot(User $user): object
    {
        return $this->competition->users()->where('users.id', $user->id)->first()->pivot;
    }

    public function test_status_change_recalculates_totals_and_ranks(): void
    {
        $aliceTrack = $this->createTrack($this->alice, ['valid_distance_meters' => 5000, 'credits_earned' => 5, 'co2_saved_grams' => 700]);
        $this->assertEquals(0, $this->pivot($this->alice)->tracks_count);

        $aliceTrack->update(['status' => TrackStatus::VALID]);

        $this->assertEquals(1, $this->pivot($this->alice)->tracks_count);
        $this->assertEquals(5.0, (float) $this->pivot($this->alice)->total_distance_km);
        $this->assertEquals(5.0, (float) $this->pivot($this->alice)->total_credits);
        $this->assertEquals(0.7, (float) $this->pivot($this->alice)->total_co2_saved_kg);
        $this->assertEquals(1, $this->pivot($this->alice)->rank);

        $this->createTrack($this->bob, ['valid_distance_meters' => 8000])->update(['status' => TrackStatus::VALID]);

        $this->assertEquals(1, $this->pivot($this->bob)->rank);
        $this->assertEquals(2, $this->pivot($this->alice)->rank);
        $this->assertEquals(2, $this->competition->fresh()->tracks_count);
    }

    public function test_manual_invalidation_removes_track_from_leaderboard(): void
    {
        $track = $this->createTrack($this->alice, ['valid_distance_meters' => 5000]);
        $track->update(['status' => TrackStatus::VALID]);

        app(TrackValidationManager::class)->invalidate($track, 'Test');

        $this->assertEquals(0, $this->pivot($this->alice)->tracks_count);
        $this->assertEquals(0.0, (float) $this->pivot($this->alice)->total_distance_km);
    }

    public function test_deleting_track_removes_it_from_leaderboard(): void
    {
        $this->createTrack($this->alice, ['valid_distance_meters' => 3000])->update(['status' => TrackStatus::VALID]);
        $track = $this->createTrack($this->alice, ['valid_distance_meters' => 5000]);
        $track->update(['status' => TrackStatus::VALID]);

        $track->delete();

        $this->assertEquals(1, $this->pivot($this->alice)->tracks_count);
        $this->assertEquals(3.0, (float) $this->pivot($this->alice)->total_distance_km);
    }

    public function test_reprocessing_a_track_does_not_double_count(): void
    {
        $track = $this->createTrack($this->alice);
        TrackSegment::create([
            'track_id' => $track->id,
            'sequence' => 0,
            'transport_mode' => TransportMode::WALK,
            'status' => 'pending',
            'distance_meters' => 2000,
            'duration_seconds' => 1200,
            'points_count' => 50,
            'max_chunk_speed_kmh' => 6.0,
        ]);

        $service = app(TrackValidationService::class);
        $service->validate($track->fresh());
        $service->validate($track->fresh());

        $this->assertEquals(1, $this->pivot($this->alice)->tracks_count);
        $this->assertEquals(2.0, (float) $this->pivot($this->alice)->total_distance_km);
    }

    public function test_refresh_command_realigns_existing_totals(): void
    {
        $this->createTrack($this->alice, ['valid_distance_meters' => 4000])->update(['status' => TrackStatus::VALID]);
        $this->competition->users()->updateExistingPivot($this->alice->id, ['total_distance_km' => 99, 'tracks_count' => 7]);

        $this->artisan('competitions:refresh-leaderboards', ['competition' => $this->competition->id])->assertSuccessful();

        $this->assertEquals(1, $this->pivot($this->alice)->tracks_count);
        $this->assertEquals(4.0, (float) $this->pivot($this->alice)->total_distance_km);
    }

    public function test_ended_competition_leaderboard_is_frozen(): void
    {
        $track = $this->createTrack($this->alice, ['valid_distance_meters' => 5000]);
        $track->update(['status' => TrackStatus::VALID]);
        $this->competition->update(['status' => CompetitionStatus::ENDED]);

        // Modifiche dopo la chiusura: invalidazione manuale, nuova traccia, cancellazione
        app(TrackValidationManager::class)->invalidate($track, 'Test');
        $this->createTrack($this->bob, ['valid_distance_meters' => 9000])->update(['status' => TrackStatus::VALID]);
        $track->delete();

        $this->artisan('competitions:refresh-leaderboards')->assertSuccessful();

        $this->assertEquals(1, $this->pivot($this->alice)->tracks_count);
        $this->assertEquals(5.0, (float) $this->pivot($this->alice)->total_distance_km);
        $this->assertEquals(1, $this->pivot($this->alice)->rank);
        $this->assertEquals(0, $this->pivot($this->bob)->tracks_count);
    }

    public function test_km_leaderboard_counts_only_credited_km(): void
    {
        $track = $this->createTrack($this->alice, ['valid_distance_meters' => 2000]);
        // 10 km in auto (non accreditati) e 3 km a piedi scartati: non contano
        $this->createSegment($track, TransportMode::CAR, 10000, 'not_credited');
        $this->createSegment($track, TransportMode::WALK, 3000, 'invalid');

        $track->update(['status' => TrackStatus::VALID]);

        $this->assertEquals(2.0, (float) $this->pivot($this->alice)->total_distance_km);
    }
}
