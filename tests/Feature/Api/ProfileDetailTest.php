<?php

namespace Tests\Feature\Api;

use App\Enums\TrackStatus;
use App\Enums\TransportMode;
use App\Models\Track;
use App\Models\TrackSegment;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileDetailTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.api.secret_key' => 'test-secret']);
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    private function createTrack(TrackStatus $status, array $segments): void
    {
        $track = Track::create([
            'user_id' => $this->user->id,
            'session_id' => 'session-' . uniqid(),
            'status' => $status,
            'started_at' => now(),
            'ended_at' => now()->addMinutes(30),
        ]);

        foreach ($segments as $sequence => [$mode, $status, $meters]) {
            TrackSegment::create([
                'track_id' => $track->id,
                'sequence' => $sequence,
                'transport_mode' => $mode,
                'status' => $status,
                'distance_meters' => $meters,
            ]);
        }
    }

    public function test_km_include_not_credited_segments_and_exclude_invalid_ones(): void
    {
        // Traccia valida: piedi valido + bici non valida + bus fuori area non accreditato
        $this->createTrack(TrackStatus::VALID, [
            [TransportMode::WALK, 'valid', 180],
            [TransportMode::BIKE, 'invalid', 5000],
            [TransportMode::BUS, 'not_credited', 2500],
        ]);
        // Traccia solo auto: non accreditata, conta solo nel totale
        $this->createTrack(TrackStatus::NOT_CREDITED, [
            [TransportMode::CAR, 'not_credited', 10000],
        ]);
        // Traccia non valida: esclusa
        $this->createTrack(TrackStatus::INVALID, [
            [TransportMode::WALK, 'invalid', 3000],
        ]);

        $response = $this->getJson('/api/v1/profile/detail', ['secret-key' => 'test-secret'])->assertOk();

        $month = strtolower(now()->translatedFormat('F'));

        $response->assertJsonPath("movements.{$month}.walk", 0.18)
            ->assertJsonPath("movements.{$month}.bicycle", 0)
            ->assertJsonPath("movements.{$month}.scooter", 0)
            ->assertJsonPath("movements.{$month}.public_transport", 2.5)
            ->assertJsonPath('stats.total_km', 12.68)
            ->assertJsonPath('stats.total_km_by_walk', 0.18)
            ->assertJsonPath('stats.total_km_by_bicycle', 0)
            ->assertJsonPath('stats.total_km_by_public_transport', 2.5);

        $this->assertCount(3, $response->json('movements'));
    }
}
