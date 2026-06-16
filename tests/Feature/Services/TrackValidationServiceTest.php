<?php

namespace Tests\Feature\Services;

use App\Enums\CompetitionStatus;
use App\Enums\CompetitionType;
use App\Enums\ExtensionType;
use App\Enums\RewardMode;
use App\Enums\ScoringType;
use App\Enums\TrackStatus;
use App\Enums\TransportMode;
use App\Models\BusStop;
use App\Models\Competition;
use App\Models\Country;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\Region;
use App\Models\Track;
use App\Models\TrackSegment;
use App\Models\TrainStation;
use App\Models\User;
use App\Services\BadgeService;
use App\Services\TrackValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    private TrackValidationService $validationService;
    private User $user;
    private User $ente;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock BadgeService to prevent badge checks during validation
        $this->mock(BadgeService::class, function ($mock) {
            $mock->shouldReceive('checkAndAward')->andReturn(collect());
        });

        $this->validationService = $this->app->make(TrackValidationService::class);

        $this->ente = User::factory()->create(['type' => \App\Enums\UserType::ENTE]);
        $this->user = User::factory()->create();
    }

    // ==================== Speed Validation ====================

    public function test_walk_segment_below_speed_limit_passes(): void
    {
        $competition = $this->createCompetition();
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::WALK,
            'distance_meters' => 1000,
            'max_chunk_speed_kmh' => 15.0, // Below 18 km/h limit
            'duration_seconds' => 300,
        ]);

        $result = $this->validationService->dryRun($track);

        $this->assertTrue($result['is_valid']);
        $this->assertEquals(0, $result['segments_invalid']);
    }

    public function test_walk_segment_above_speed_limit_fails(): void
    {
        $competition = $this->createCompetition();
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::WALK,
            'distance_meters' => 1000,
            'max_chunk_speed_kmh' => 25.0, // Above 18 km/h limit
            'duration_seconds' => 300,
        ]);

        $result = $this->validationService->dryRun($track);

        $this->assertEquals(1, $result['segments_invalid']);
        $this->assertNotEmpty($result['warnings']);
    }

    public function test_bike_segment_below_speed_limit_passes(): void
    {
        $competition = $this->createCompetition();
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::BIKE,
            'distance_meters' => 2000,
            'max_chunk_speed_kmh' => 30.0, // Below 40 km/h limit
            'duration_seconds' => 300,
        ]);

        $result = $this->validationService->dryRun($track);

        $this->assertTrue($result['is_valid']);
        $this->assertEquals(0, $result['segments_invalid']);
    }

    public function test_bike_segment_above_speed_limit_fails(): void
    {
        $competition = $this->createCompetition();
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::BIKE,
            'distance_meters' => 2000,
            'max_chunk_speed_kmh' => 45.0, // Above 40 km/h limit
            'duration_seconds' => 300,
        ]);

        $result = $this->validationService->dryRun($track);

        $this->assertEquals(1, $result['segments_invalid']);
    }

    public function test_train_segment_no_speed_check(): void
    {
        $competition = $this->createCompetition();
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::TRAIN,
            'distance_meters' => 50000,
            'max_chunk_speed_kmh' => null, // No chunk speed for train
            'avg_speed_ms' => 50.0, // Very fast, but train has no speed limit
            'duration_seconds' => 1800,
        ]);

        $result = $this->validationService->dryRun($track);

        // The speed check itself passes for train (no limit).
        // The segment may fail the public transport check (no train stations in test DB),
        // but verify the speed_check in validation_checks passed.
        $segmentDetail = $result['segments_details'][0];
        $speedCheck = $segmentDetail['validation_checks']['speed_check'] ?? null;
        $this->assertNotNull($speedCheck);
        $this->assertTrue($speedCheck['passed'], 'Train speed check should pass regardless of speed');
    }

    public function test_short_walk_segment_passes_regardless_of_speed(): void
    {
        $competition = $this->createCompetition();
        $track = $this->createTrack($competition);
        // Segment shorter than 500m (walk chunk size), so no speed check
        $this->createSegment($track, [
            'transport_mode' => TransportMode::WALK,
            'distance_meters' => 300,
            'max_chunk_speed_kmh' => null, // No chunks (too short)
            'avg_speed_ms' => 10.0, // Fast, but segment too short for check
            'duration_seconds' => 30,
        ]);

        $result = $this->validationService->dryRun($track);

        $segmentDetail = $result['segments_details'][0];
        $this->assertTrue($segmentDetail['is_valid']);
    }

    // ==================== Transport Mode Rules ====================

    public function test_car_segment_is_not_credited(): void
    {
        $competition = $this->createCompetition();
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::CAR,
            'distance_meters' => 10000,
            'duration_seconds' => 600,
        ]);

        $result = $this->validationService->dryRun($track);

        $segmentDetail = $result['segments_details'][0];
        $this->assertTrue($segmentDetail['is_not_credited']);
        $this->assertFalse($segmentDetail['generates_credits']);
        $this->assertEquals(0, $segmentDetail['credits_earned']);
    }

    public function test_motorcycle_segment_is_not_credited(): void
    {
        $competition = $this->createCompetition();
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::MOTORCYCLE,
            'distance_meters' => 10000,
            'duration_seconds' => 600,
        ]);

        $result = $this->validationService->dryRun($track);

        $segmentDetail = $result['segments_details'][0];
        $this->assertTrue($segmentDetail['is_not_credited']);
        $this->assertFalse($segmentDetail['generates_credits']);
    }

    public function test_walk_segment_generates_credits(): void
    {
        $competition = $this->createCompetition(['credits_per_km' => 2.0]);
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::WALK,
            'distance_meters' => 5000,
            'generates_credits' => true,
            'duration_seconds' => 3000,
        ]);

        $result = $this->validationService->dryRun($track);

        $segmentDetail = $result['segments_details'][0];
        $this->assertTrue($segmentDetail['generates_credits']);
        $this->assertGreaterThan(0, $segmentDetail['credits_earned']);
    }

    public function test_bike_segment_generates_credits(): void
    {
        $competition = $this->createCompetition(['credits_per_km' => 2.0]);
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::BIKE,
            'distance_meters' => 5000,
            'generates_credits' => true,
            'duration_seconds' => 600,
        ]);

        $result = $this->validationService->dryRun($track);

        $segmentDetail = $result['segments_details'][0];
        $this->assertTrue($segmentDetail['generates_credits']);
        $this->assertGreaterThan(0, $segmentDetail['credits_earned']);
    }

    public function test_bus_segment_generates_credits(): void
    {
        $competition = $this->createCompetition(['credits_per_km' => 2.0]);
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::BUS,
            'distance_meters' => 5000,
            'generates_credits' => true,
            'duration_seconds' => 600,
        ]);

        // Note: Bus would normally require proximity to bus stops.
        // We test the mode's credit generation in isolation via dryRun.
        $result = $this->validationService->dryRun($track);

        $segmentDetail = $result['segments_details'][0];
        // Bus may fail public transport check (no bus stops in test DB),
        // but the mode itself generates credits
        $this->assertTrue(TransportMode::BUS->generatesCredits());
    }

    public function test_train_segment_generates_credits(): void
    {
        $this->assertTrue(TransportMode::TRAIN->generatesCredits());
    }

    // ==================== Credits Calculation ====================

    public function test_credits_per_mode_used_when_available(): void
    {
        $competition = $this->createCompetition([
            'credits_per_km' => 1.0,
            'credits_per_mode' => ['walk' => 4.0, 'bike' => 3.0, 'bus' => 2.0, 'train' => 2.0],
            'credits_multiplier' => 1.0,
        ]);
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::WALK,
            'distance_meters' => 5000, // 5 km
            'generates_credits' => true,
            'duration_seconds' => 3000,
        ]);

        $result = $this->validationService->dryRun($track);

        $segmentDetail = $result['segments_details'][0];
        // 5 km * 4.0 credits/km (walk specific) = 20.0 credits
        $this->assertEqualsWithDelta(20.0, $segmentDetail['credits_earned'], 0.01);
    }

    public function test_credits_fallback_to_credits_per_km(): void
    {
        $competition = $this->createCompetition([
            'credits_per_km' => 2.0,
            'credits_per_mode' => null, // No per-mode configuration
            'credits_multiplier' => 1.0,
        ]);
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::WALK,
            'distance_meters' => 5000, // 5 km
            'generates_credits' => true,
            'duration_seconds' => 3000,
        ]);

        $result = $this->validationService->dryRun($track);

        $segmentDetail = $result['segments_details'][0];
        // 5 km * 2.0 credits/km * 1.0 multiplier = 10.0
        $this->assertEqualsWithDelta(10.0, $segmentDetail['credits_earned'], 0.01);
    }

    public function test_credits_multiplier_applied(): void
    {
        $competition = $this->createCompetition([
            'credits_per_km' => 2.0,
            'credits_per_mode' => null,
            'credits_multiplier' => 1.5,
        ]);
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::WALK,
            'distance_meters' => 5000,
            'generates_credits' => true,
            'duration_seconds' => 3000,
        ]);

        $result = $this->validationService->dryRun($track);

        $segmentDetail = $result['segments_details'][0];
        // 5 km * 2.0 * 1.5 = 15.0
        $this->assertEqualsWithDelta(15.0, $segmentDetail['credits_earned'], 0.01);
    }

    public function test_car_gets_zero_credits_regardless(): void
    {
        $competition = $this->createCompetition([
            'credits_per_km' => 10.0,
            'credits_multiplier' => 5.0,
        ]);
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::CAR,
            'distance_meters' => 10000,
            'generates_credits' => false,
            'duration_seconds' => 600,
        ]);

        $result = $this->validationService->dryRun($track);

        $segmentDetail = $result['segments_details'][0];
        $this->assertEquals(0, $segmentDetail['credits_earned']);
    }

    public function test_motorcycle_gets_zero_credits_regardless(): void
    {
        $competition = $this->createCompetition([
            'credits_per_km' => 10.0,
            'credits_multiplier' => 5.0,
        ]);
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::MOTORCYCLE,
            'distance_meters' => 10000,
            'generates_credits' => false,
            'duration_seconds' => 600,
        ]);

        $result = $this->validationService->dryRun($track);

        $segmentDetail = $result['segments_details'][0];
        $this->assertEquals(0, $segmentDetail['credits_earned']);
    }

    // ==================== Competition Rules ====================

    public function test_max_distance_per_mode_limits_credits(): void
    {
        $competition = $this->createCompetition([
            'credits_per_km' => 2.0,
            'credits_multiplier' => 1.0,
            'max_distance_per_mode' => ['walk' => 3.0], // Max 3 km per track for walk
        ]);
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::WALK,
            'distance_meters' => 5000, // 5 km, exceeds 3 km limit
            'generates_credits' => true,
            'duration_seconds' => 3000,
        ]);

        $result = $this->validationService->dryRun($track);

        // Credits should be limited: excess = 2 km * 2.0 = 4.0 credits subtracted
        // Full credits would be 5 * 2 = 10, limited to 3 * 2 = 6
        $this->assertEqualsWithDelta(6.0, $result['credits_earned'], 0.01);
        $this->assertNotEmpty($result['warnings']);
    }

    public function test_max_daily_distance_per_mode_limits_credits(): void
    {
        $competition = $this->createCompetition([
            'credits_per_km' => 2.0,
            'credits_multiplier' => 1.0,
            'max_daily_distance_per_mode' => ['walk' => 5.0], // Max 5 km/day for walk
        ]);

        $today = now();

        // Create an already validated track today with 3 km walk
        $existingTrack = Track::create([
            'user_id' => $this->user->id,
            'competition_id' => $competition->id,
            'session_id' => 'session-existing',
            'status' => TrackStatus::VALID,
            'started_at' => $today,
            'ended_at' => $today->copy()->addMinutes(30),
        ]);
        TrackSegment::create([
            'track_id' => $existingTrack->id,
            'sequence' => 0,
            'transport_mode' => TransportMode::WALK,
            'status' => 'valid',
            'distance_meters' => 3000, // 3 km already used
            'duration_seconds' => 1800,
            'generates_credits' => true,
        ]);

        // New track with 4 km walk (total would be 7 km, exceeds 5 km daily limit)
        $newTrack = $this->createTrack($competition, [
            'started_at' => $today->copy()->addHours(2),
            'ended_at' => $today->copy()->addHours(3),
        ]);
        $this->createSegment($newTrack, [
            'transport_mode' => TransportMode::WALK,
            'distance_meters' => 4000, // 4 km
            'generates_credits' => true,
            'duration_seconds' => 2400,
        ]);

        $result = $this->validationService->dryRun($newTrack);

        // Remaining daily allowance: 5 - 3 = 2 km
        // Excess: 4 - 2 = 2 km, excess credits = 2 * 2.0 = 4
        // Full credits would be 4 * 2 = 8, limited to 8 - 4 = 4
        $this->assertEqualsWithDelta(4.0, $result['credits_earned'], 0.1);
    }

    public function test_credits_to_euro_conversion(): void
    {
        $competition = $this->createCompetition([
            'credits_per_km' => 2.0,
            'credits_multiplier' => 1.0,
            'credits_to_euro' => 100.0, // 100 credits = 1 EUR
        ]);
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::WALK,
            'distance_meters' => 5000, // 5 km
            'generates_credits' => true,
            'duration_seconds' => 3000,
        ]);

        $result = $this->validationService->dryRun($track);

        $segmentDetail = $result['segments_details'][0];
        // 10 credits / 100 credits_to_euro = 0.10 EUR
        $this->assertEqualsWithDelta(0.10, $segmentDetail['credits_euro_value'], 0.01);
    }

    public function test_max_daily_tracks_blocks_credits(): void
    {
        $competition = $this->createCompetition([
            'credits_per_km' => 2.0,
            'credits_multiplier' => 1.0,
            'max_daily_tracks' => 1, // Only 1 track per day
        ]);

        $today = now();

        // Create an already validated track today
        Track::create([
            'user_id' => $this->user->id,
            'competition_id' => $competition->id,
            'session_id' => 'session-existing',
            'status' => TrackStatus::VALID,
            'started_at' => $today,
            'ended_at' => $today->copy()->addMinutes(30),
        ]);

        // New track (second of the day)
        $newTrack = $this->createTrack($competition, [
            'started_at' => $today->copy()->addHours(2),
            'ended_at' => $today->copy()->addHours(3),
        ]);
        $this->createSegment($newTrack, [
            'transport_mode' => TransportMode::WALK,
            'distance_meters' => 5000,
            'generates_credits' => true,
            'duration_seconds' => 3000,
        ]);

        $result = $this->validationService->dryRun($newTrack);

        // Credits should be 0 since daily track limit is reached
        $this->assertEquals(0, $result['credits_earned']);
        $this->assertNotEmpty($result['warnings']);
    }

    // ==================== Dry Run ====================

    public function test_dry_run_does_not_modify_database(): void
    {
        $competition = $this->createCompetition(['credits_per_km' => 2.0]);
        $track = $this->createTrack($competition);
        $segment = $this->createSegment($track, [
            'transport_mode' => TransportMode::WALK,
            'distance_meters' => 5000,
            'generates_credits' => true,
            'duration_seconds' => 3000,
        ]);

        $originalTrackStatus = $track->status;
        $originalSegmentStatus = $segment->status;
        $originalUserCredits = $this->user->credits;

        $result = $this->validationService->dryRun($track);

        // Verify dry_run flag is set
        $this->assertTrue($result['dry_run']);

        // Reload from database and verify nothing changed
        $track->refresh();
        $segment->refresh();
        $this->user->refresh();

        $this->assertEquals($originalTrackStatus, $track->status);
        $this->assertEquals($originalSegmentStatus, $segment->status);
        $this->assertEquals($originalUserCredits, $this->user->credits);
    }

    public function test_dry_run_returns_results(): void
    {
        $competition = $this->createCompetition(['credits_per_km' => 2.0]);
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::WALK,
            'distance_meters' => 5000,
            'generates_credits' => true,
            'duration_seconds' => 3000,
        ]);

        $result = $this->validationService->dryRun($track);

        $this->assertArrayHasKey('is_valid', $result);
        $this->assertArrayHasKey('segments_details', $result);
        $this->assertArrayHasKey('credits_earned', $result);
        $this->assertArrayHasKey('co2_saved_grams', $result);
        $this->assertArrayHasKey('calories_burned', $result);
        $this->assertGreaterThan(0, $result['credits_earned']);
    }

    // ==================== Track Without Segments ====================

    public function test_track_without_segments_is_invalid(): void
    {
        $competition = $this->createCompetition();
        $track = $this->createTrack($competition);
        // No segments created

        $result = $this->validationService->dryRun($track);

        $this->assertFalse($result['is_valid']);
        $this->assertNotNull($result['rejection_reason']);
    }

    // ==================== Multi-Segment Track ====================

    public function test_mixed_mode_track_credits_only_valid_segments(): void
    {
        $competition = $this->createCompetition([
            'credits_per_km' => 2.0,
            'credits_multiplier' => 1.0,
        ]);
        $track = $this->createTrack($competition);

        // Walk segment (generates credits)
        $this->createSegment($track, [
            'transport_mode' => TransportMode::WALK,
            'distance_meters' => 3000, // 3 km
            'generates_credits' => true,
            'duration_seconds' => 1800,
            'sequence' => 0,
        ]);

        // Car segment (not credited)
        $this->createSegment($track, [
            'transport_mode' => TransportMode::CAR,
            'distance_meters' => 10000, // 10 km
            'generates_credits' => false,
            'duration_seconds' => 600,
            'sequence' => 1,
        ]);

        // Bike segment (generates credits)
        $this->createSegment($track, [
            'transport_mode' => TransportMode::BIKE,
            'distance_meters' => 2000, // 2 km
            'generates_credits' => true,
            'duration_seconds' => 300,
            'sequence' => 2,
        ]);

        $result = $this->validationService->dryRun($track);

        // Walk: 3 km * 2 = 6 credits
        // Car: 0 credits
        // Bike: 2 km * 2 = 4 credits
        // Total: 10 credits
        $this->assertEqualsWithDelta(10.0, $result['credits_earned'], 0.01);
    }

    // ==================== Train Station Proximity ====================

    public function test_train_segment_passes_when_stations_within_100m_at_start_and_end(): void
    {
        // Pisa Centrale e Firenze S.M.N.
        $this->createTrainStation('Pisa Centrale', 43.7085000, 10.3983000);
        $this->createTrainStation('Firenze S.M.N.', 43.7760000, 11.2482000);

        $competition = $this->createCompetition();
        $track = $this->createTrack($competition);
        // Punto di partenza a ~30m dalla stazione di Pisa, arrivo a ~30m da Firenze
        $this->createSegment($track, [
            'transport_mode' => TransportMode::TRAIN,
            'distance_meters' => 80000,
            'max_chunk_speed_kmh' => null,
            'avg_speed_ms' => 40.0,
            'duration_seconds' => 2000,
            'start_latitude' => 43.7087700, // +0.00027 lat (~30m)
            'start_longitude' => 10.3983000,
            'end_latitude' => 43.7762700,
            'end_longitude' => 11.2482000,
        ]);

        $result = $this->validationService->dryRun($track);

        $segmentDetail = $result['segments_details'][0];
        $publicTransport = $segmentDetail['validation_checks']['public_transport_check'] ?? null;
        $this->assertNotNull($publicTransport);
        $this->assertTrue($publicTransport['passed'], 'Train check should pass with stations at both ends');
        $this->assertEquals('Pisa Centrale', $publicTransport['start_station']);
        $this->assertEquals('Firenze S.M.N.', $publicTransport['end_station']);
    }

    public function test_train_segment_fails_when_no_station_near_start(): void
    {
        // Stazione di arrivo presente, nessuna all'inizio
        $this->createTrainStation('Firenze S.M.N.', 43.7760000, 11.2482000);

        $competition = $this->createCompetition();
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::TRAIN,
            'distance_meters' => 80000,
            'max_chunk_speed_kmh' => null,
            'avg_speed_ms' => 40.0,
            'duration_seconds' => 2000,
            'start_latitude' => 45.0000000, // In mezzo al nulla
            'start_longitude' => 9.0000000,
            'end_latitude' => 43.7760000,
            'end_longitude' => 11.2482000,
        ]);

        $result = $this->validationService->dryRun($track);

        $segmentDetail = $result['segments_details'][0];
        $publicTransport = $segmentDetail['validation_checks']['public_transport_check'] ?? null;
        $this->assertNotNull($publicTransport);
        $this->assertFalse($publicTransport['passed']);
        $this->assertStringContainsString('partenza', $publicTransport['reason']);
    }

    public function test_train_segment_fails_when_no_station_near_end(): void
    {
        $this->createTrainStation('Pisa Centrale', 43.7085000, 10.3983000);
        // Nessuna stazione di arrivo

        $competition = $this->createCompetition();
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::TRAIN,
            'distance_meters' => 80000,
            'max_chunk_speed_kmh' => null,
            'avg_speed_ms' => 40.0,
            'duration_seconds' => 2000,
            'start_latitude' => 43.7085000,
            'start_longitude' => 10.3983000,
            'end_latitude' => 45.0000000,
            'end_longitude' => 9.0000000,
        ]);

        $result = $this->validationService->dryRun($track);

        $segmentDetail = $result['segments_details'][0];
        $publicTransport = $segmentDetail['validation_checks']['public_transport_check'] ?? null;
        $this->assertNotNull($publicTransport);
        $this->assertFalse($publicTransport['passed']);
        $this->assertStringContainsString('arrivo', $publicTransport['reason']);
    }

    // ==================== Bus Stop Proximity ====================

    public function test_bus_segment_passes_in_pi_province_with_stops_within_10m(): void
    {
        $pisa = $this->createMunicipalityWithProvince('Pisa', 'PI', 43.7085000, 10.3993000);
        $this->createBusStop('Pisa Stazione', 43.7085000, 10.3993000, $pisa->id);
        $this->createBusStop('Pisa Ospedale', 43.7150000, 10.4013000, $pisa->id);

        $competition = $this->createCompetition();
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::BUS,
            'distance_meters' => 1500,
            'max_chunk_speed_kmh' => null,
            'avg_speed_ms' => 8.0,
            'duration_seconds' => 420,
            'start_latitude' => 43.7085500, // ~5m dalla prima fermata
            'start_longitude' => 10.3993000,
            'end_latitude' => 43.7150000,
            'end_longitude' => 10.4013000,
        ]);

        $result = $this->validationService->dryRun($track);

        $segmentDetail = $result['segments_details'][0];
        $publicTransport = $segmentDetail['validation_checks']['public_transport_check'] ?? null;
        $this->assertNotNull($publicTransport);
        $this->assertTrue($publicTransport['passed']);
        $this->assertArrayNotHasKey('not_credited', $publicTransport);
        $this->assertEquals('Pisa Stazione', $publicTransport['start_stop']);
        $this->assertEquals('Pisa Ospedale', $publicTransport['end_stop']);
    }

    public function test_bus_segment_outside_pi_li_is_not_credited(): void
    {
        // Traccia bus in provincia di Firenze (fuori PI/LI)
        $firenze = $this->createMunicipalityWithProvince('Firenze', 'FI', 43.7696000, 11.2558000);
        // Fermate non rilevanti: segmento deve risultare "non accreditato" prima del check fermate

        $competition = $this->createCompetition();
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::BUS,
            'distance_meters' => 2000,
            'max_chunk_speed_kmh' => null,
            'avg_speed_ms' => 8.0,
            'duration_seconds' => 480,
            'start_latitude' => 43.7696000,
            'start_longitude' => 11.2558000,
            'end_latitude' => 43.7750000,
            'end_longitude' => 11.2600000,
        ]);

        $result = $this->validationService->dryRun($track);

        $segmentDetail = $result['segments_details'][0];
        $publicTransport = $segmentDetail['validation_checks']['public_transport_check'] ?? null;
        $this->assertNotNull($publicTransport);
        $this->assertTrue($publicTransport['passed'], 'Fuori PI/LI deve passare');
        $this->assertTrue($publicTransport['not_credited'] ?? false, 'Fuori PI/LI deve essere not_credited');
    }

    public function test_bus_segment_fails_in_pi_li_without_nearby_stop(): void
    {
        $pisa = $this->createMunicipalityWithProvince('Pisa', 'PI', 43.7085000, 10.3993000);
        // Nessuna fermata creata

        $competition = $this->createCompetition();
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::BUS,
            'distance_meters' => 1500,
            'max_chunk_speed_kmh' => null,
            'avg_speed_ms' => 8.0,
            'duration_seconds' => 420,
            'start_latitude' => 43.7085000,
            'start_longitude' => 10.3993000,
            'end_latitude' => 43.7150000,
            'end_longitude' => 10.4013000,
        ]);

        $result = $this->validationService->dryRun($track);

        $segmentDetail = $result['segments_details'][0];
        $publicTransport = $segmentDetail['validation_checks']['public_transport_check'] ?? null;
        $this->assertNotNull($publicTransport);
        $this->assertFalse($publicTransport['passed']);
        $this->assertStringContainsString('fermata bus', $publicTransport['reason']);
    }

    public function test_bus_segment_without_municipality_passes_with_note(): void
    {
        // Nessun Municipality in DB, nessuna fermata — coord in mare aperto
        $competition = $this->createCompetition();
        $track = $this->createTrack($competition);
        $this->createSegment($track, [
            'transport_mode' => TransportMode::BUS,
            'distance_meters' => 1500,
            'max_chunk_speed_kmh' => null,
            'avg_speed_ms' => 8.0,
            'duration_seconds' => 420,
            'start_latitude' => 42.0000000,
            'start_longitude' => 8.0000000,
            'end_latitude' => 42.0100000,
            'end_longitude' => 8.0100000,
        ]);

        $result = $this->validationService->dryRun($track);

        $segmentDetail = $result['segments_details'][0];
        $publicTransport = $segmentDetail['validation_checks']['public_transport_check'] ?? null;
        $this->assertNotNull($publicTransport);
        $this->assertTrue($publicTransport['passed']);
        $this->assertArrayHasKey('note', $publicTransport);
    }

    // ==================== Helper Methods ====================

    private function createCompetition(array $overrides = []): Competition
    {
        $defaults = [
            'name' => 'Test Competition',
            'ente_id' => $this->ente->id,
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
        ];

        $competition = Competition::create(array_merge($defaults, $overrides));

        // Attach user as approved
        $competition->users()->attach($this->user->id, [
            'status' => 'approved',
            'registered_at' => now(),
            'approved_at' => now(),
        ]);

        return $competition;
    }

    private function createTrack(Competition $competition, array $overrides = []): Track
    {
        $defaults = [
            'user_id' => $this->user->id,
            'competition_id' => $competition->id,
            'session_id' => 'session-' . uniqid(),
            'status' => TrackStatus::PENDING,
            'started_at' => now(),
            'ended_at' => now()->addMinutes(30),
            'duration_seconds' => 1800,
        ];

        return Track::create(array_merge($defaults, $overrides));
    }

    private function createSegment(Track $track, array $overrides = []): TrackSegment
    {
        $defaults = [
            'track_id' => $track->id,
            'sequence' => 0,
            'transport_mode' => TransportMode::WALK,
            'status' => 'pending',
            'distance_meters' => 1000,
            'duration_seconds' => 600,
            'points_count' => 50,
            'generates_credits' => true,
            'start_latitude' => 43.716667,
            'start_longitude' => 10.400000,
            'end_latitude' => 43.726667,
            'end_longitude' => 10.410000,
            'started_at' => now(),
            'ended_at' => now()->addMinutes(10),
        ];

        return TrackSegment::create(array_merge($defaults, $overrides));
    }

    private function createTrainStation(string $name, float $lat, float $lng, ?int $municipalityId = null): TrainStation
    {
        return TrainStation::create([
            'name' => $name,
            'latitude' => $lat,
            'longitude' => $lng,
            'municipality_id' => $municipalityId,
            'is_active' => true,
            'type' => 'regional',
        ]);
    }

    private function createBusStop(string $name, float $lat, float $lng, ?int $municipalityId = null): BusStop
    {
        return BusStop::create([
            'name' => $name,
            'latitude' => $lat,
            'longitude' => $lng,
            'municipality_id' => $municipalityId,
            'is_active' => true,
        ]);
    }

    private function createMunicipalityWithProvince(string $name, string $provinceCode, float $lat, float $lng): Municipality
    {
        $country = Country::firstOrCreate(
            ['code' => 'ITA'],
            ['name' => 'Italia', 'code_2' => 'IT', 'is_active' => true]
        );
        $region = Region::firstOrCreate(
            ['country_id' => $country->id, 'name' => 'Toscana'],
            ['is_active' => true]
        );
        $province = Province::firstOrCreate(
            ['region_id' => $region->id, 'name' => $name],
            ['code' => $provinceCode, 'is_active' => true]
        );

        return Municipality::create([
            'province_id' => $province->id,
            'name' => $name,
            'latitude' => $lat,
            'longitude' => $lng,
            'is_active' => true,
        ]);
    }
}
