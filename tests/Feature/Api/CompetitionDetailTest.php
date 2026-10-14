<?php

namespace Tests\Feature\Api;

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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CompetitionDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_results_contain_the_competition_totals(): void
    {
        config(['services.api.secret_key' => 'test-secret']);

        $ente = User::factory()->create(['type' => UserType::ENTE]);
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        $competition = Competition::create([
            'name' => 'Gara risultati',
            'ente_id' => $ente->id,
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(30),
            'status' => CompetitionStatus::ACTIVE,
            'is_public' => true,
            'extension_type' => ExtensionType::NATIONAL,
            'competition_type' => CompetitionType::NATIONAL,
            'reward_mode' => RewardMode::CREDITS_BASED,
            'scoring_type' => ScoringType::DISTANCE,
            'credits_per_km' => 1.0,
            'credits_multiplier' => 1.0,
            'credits_to_euro' => 0,
            'max_daily_tracks' => 99,
        ]);

        foreach ([$alice, $bob] as $user) {
            $competition->users()->attach($user->id, [
                'status' => 'approved',
                'registered_at' => now(),
                'approved_at' => now(),
            ]);
        }

        $tracks = [
            [$alice, TrackStatus::VALID, 4250, 600],
            [$bob, TrackStatus::VALID, 1500, 210],
            [$bob, TrackStatus::INVALID, 9000, 0],
            [$alice, TrackStatus::PENDING, 0, 0],
        ];
        foreach ($tracks as [$user, $status, $meters, $co2]) {
            $track = Track::create([
                'user_id' => $user->id,
                'competition_id' => $competition->id,
                'session_id' => 'session-' . uniqid(),
                'status' => $status,
                'valid_distance_meters' => $meters,
                'co2_saved_grams' => $co2,
                'started_at' => now(),
                'ended_at' => now()->addMinutes(30),
            ]);
            TrackSegment::create([
                'track_id' => $track->id,
                'sequence' => 0,
                'transport_mode' => TransportMode::WALK,
                'status' => $status === TrackStatus::VALID ? 'valid' : 'invalid',
                'generates_credits' => $status === TrackStatus::VALID,
                'distance_meters' => $meters,
            ]);
        }
        $competition->refreshLeaderboard();

        Sanctum::actingAs($alice);

        $this->getJson("/api/v1/competition/{$competition->id}", ['secret-key' => 'test-secret'])
            ->assertOk()
            ->assertJsonPath('results', [
                ['type' => 'participants', 'value' => 2],
                ['type' => 'tracks', 'value' => 4],
                ['type' => 'valid_tracks', 'value' => 2],
                ['type' => 'km', 'value' => 5.75],
                ['type' => 'co2', 'value' => 0.81],
            ]);
    }
}
