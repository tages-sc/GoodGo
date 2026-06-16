<?php

namespace Tests\Unit\Services;

use App\Enums\TransportMode;
use App\Services\TrackParserService;
use PHPUnit\Framework\TestCase;

class TrackParserServiceTest extends TestCase
{
    private TrackParserService $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new TrackParserService();
    }

    // ==================== GPS Data Cleaning ====================

    public function test_filter_by_accuracy_removes_points_above_threshold(): void
    {
        $points = [
            ['accuracy' => 5.0, 'latitude' => 43.7, 'longitude' => 10.4],
            ['accuracy' => 25.0, 'latitude' => 43.7, 'longitude' => 10.4],  // Over 20m
            ['accuracy' => 15.0, 'latitude' => 43.7, 'longitude' => 10.4],
            ['accuracy' => 20.0, 'latitude' => 43.7, 'longitude' => 10.4],  // Exactly 20m, should pass
            ['accuracy' => 20.1, 'latitude' => 43.7, 'longitude' => 10.4],  // Just over, removed
        ];

        $filtered = $this->parser->filterByAccuracy($points, 20.0);

        $this->assertCount(3, $filtered);
        $this->assertEquals(5.0, $filtered[0]['accuracy']);
        $this->assertEquals(15.0, $filtered[1]['accuracy']);
        $this->assertEquals(20.0, $filtered[2]['accuracy']);
    }

    public function test_filter_by_accuracy_keeps_all_if_below_threshold(): void
    {
        $points = [
            ['accuracy' => 5.0],
            ['accuracy' => 10.0],
            ['accuracy' => 15.0],
        ];

        $filtered = $this->parser->filterByAccuracy($points, 20.0);

        $this->assertCount(3, $filtered);
    }

    public function test_filter_by_max_absolute_speed_removes_points_above_150ms(): void
    {
        $points = [
            ['speed' => 5.0],
            ['speed' => 150.0],   // Exactly 150 m/s, should pass
            ['speed' => 150.1],   // Over limit
            ['speed' => 200.0],   // Over limit
            ['speed' => 0.0],
        ];

        $filtered = $this->parser->filterByMaxAbsoluteSpeed($points, 150.0);

        $this->assertCount(3, $filtered);
        $this->assertEquals(5.0, $filtered[0]['speed']);
        $this->assertEquals(150.0, $filtered[1]['speed']);
        $this->assertEquals(0.0, $filtered[2]['speed']);
    }

    public function test_parse_applies_accuracy_and_speed_filters(): void
    {
        // Build CSV with some invalid points
        $csv = $this->buildCsv([
            // Good point
            ['latitude' => 43.716667, 'longitude' => 10.4, 'accuracy' => 10.0, 'speed' => 1.5, 'timeStamp' => 1000000, 'vehicleMode' => 1, 'sessionId' => 'test-session'],
            // Bad accuracy (30m > 20m)
            ['latitude' => 43.717000, 'longitude' => 10.401, 'accuracy' => 30.0, 'speed' => 1.5, 'timeStamp' => 1001000, 'vehicleMode' => 1, 'sessionId' => 'test-session'],
            // Bad speed (200 m/s > 150 m/s)
            ['latitude' => 43.717500, 'longitude' => 10.402, 'accuracy' => 5.0, 'speed' => 200.0, 'timeStamp' => 1002000, 'vehicleMode' => 1, 'sessionId' => 'test-session'],
            // Good point
            ['latitude' => 43.718000, 'longitude' => 10.403, 'accuracy' => 8.0, 'speed' => 2.0, 'timeStamp' => 1003000, 'vehicleMode' => 1, 'sessionId' => 'test-session'],
        ]);

        $result = $this->parser->parse($csv);

        // Only the 2 good points should remain
        $this->assertCount(2, $result['points']);
        $this->assertEquals(10.0, $result['points'][0]['accuracy']);
        $this->assertEquals(8.0, $result['points'][1]['accuracy']);
    }

    // ==================== Vincenty Distance ====================

    public function test_vincenty_distance_same_point_returns_zero(): void
    {
        $distance = $this->parser->vincentyDistance(43.716667, 10.4, 43.716667, 10.4);

        $this->assertEquals(0.0, $distance);
    }

    public function test_vincenty_distance_known_coordinates(): void
    {
        // Rome (Colosseum) to Milan (Duomo), ~477 km
        $distance = $this->parser->vincentyDistance(
            41.890251, 12.492373,  // Rome
            45.464211, 9.191383    // Milan
        );

        $distanceKm = $distance / 1000;
        // Should be approximately 477 km (within 2% tolerance)
        $this->assertEqualsWithDelta(477, $distanceKm, 10);
    }

    public function test_vincenty_distance_short_distance_accuracy(): void
    {
        // Two points roughly 100 meters apart in Pisa
        $distance = $this->parser->vincentyDistance(
            43.716667, 10.400000,
            43.717567, 10.400000  // ~100m north
        );

        // Should be approximately 100 meters (within 5m tolerance)
        $this->assertEqualsWithDelta(100, $distance, 5);
    }

    public function test_vincenty_distance_very_short_distance(): void
    {
        // Two points roughly 10 meters apart
        $distance = $this->parser->vincentyDistance(
            43.716667, 10.400000,
            43.716757, 10.400000  // ~10m north
        );

        $this->assertEqualsWithDelta(10, $distance, 2);
    }

    // ==================== Chunk Speed Calculation ====================

    public function test_chunk_speed_walk_500m_chunks(): void
    {
        // Create a walk segment covering ~1000m in 600 seconds (1.67 m/s = 6 km/h)
        // We need points spaced to accumulate 500m in each chunk
        $points = $this->generateLinearPoints(
            startLat: 43.716667,
            startLon: 10.400000,
            totalDistanceMeters: 1000,
            totalTimeMs: 600000,    // 600 seconds
            numPoints: 20
        );

        $result = $this->parser->calculateChunkSpeeds($points, 500);

        $this->assertNotEmpty($result['chunks']);
        // Speed should be around 6 km/h (1000m / 600s * 3.6)
        $this->assertGreaterThan(0, $result['max_speed_kmh']);
        $this->assertLessThan(18, $result['max_speed_kmh']); // Below walk limit
    }

    public function test_chunk_speed_bike_800m_chunks(): void
    {
        // Create a bike segment covering ~1600m in 240 seconds (6.67 m/s = 24 km/h)
        $points = $this->generateLinearPoints(
            startLat: 43.716667,
            startLon: 10.400000,
            totalDistanceMeters: 1600,
            totalTimeMs: 240000,    // 240 seconds
            numPoints: 30
        );

        $result = $this->parser->calculateChunkSpeeds($points, 800);

        $this->assertNotEmpty($result['chunks']);
        $this->assertGreaterThan(0, $result['max_speed_kmh']);
        $this->assertLessThan(40, $result['max_speed_kmh']); // Below bike limit
    }

    public function test_chunk_speed_segment_shorter_than_chunk_size_returns_empty(): void
    {
        // Segment of 300m, less than 500m chunk size
        $points = $this->generateLinearPoints(
            startLat: 43.716667,
            startLon: 10.400000,
            totalDistanceMeters: 300,
            totalTimeMs: 200000,
            numPoints: 10
        );

        $result = $this->parser->calculateChunkSpeeds($points, 500);

        // No complete chunks formed
        $this->assertEmpty($result['chunks']);
        $this->assertEquals(0, $result['max_speed_kmh']);
    }

    public function test_chunk_speed_detects_fast_chunk_among_slow_ones(): void
    {
        // Build points: slow-slow-FAST-slow
        // First 500m slow (300 seconds = 6 km/h)
        $slowPoints1 = $this->generateLinearPoints(
            startLat: 43.716667,
            startLon: 10.400000,
            totalDistanceMeters: 500,
            totalTimeMs: 300000,
            numPoints: 10
        );

        // Next 500m FAST (30 seconds = 60 km/h, well over 18 km/h walk limit)
        $lastSlow = end($slowPoints1);
        $fastPoints = $this->generateLinearPoints(
            startLat: $lastSlow['latitude'],
            startLon: $lastSlow['longitude'],
            totalDistanceMeters: 500,
            totalTimeMs: 30000,
            numPoints: 10,
            startTimeMs: $lastSlow['timestamp']
        );
        // Remove first point of fast segment (overlaps with last of slow)
        array_shift($fastPoints);

        // Another 500m slow
        $lastFast = end($fastPoints);
        $slowPoints2 = $this->generateLinearPoints(
            startLat: $lastFast['latitude'],
            startLon: $lastFast['longitude'],
            totalDistanceMeters: 500,
            totalTimeMs: 300000,
            numPoints: 10,
            startTimeMs: $lastFast['timestamp']
        );
        array_shift($slowPoints2);

        $allPoints = array_merge($slowPoints1, $fastPoints, $slowPoints2);

        $result = $this->parser->calculateChunkSpeeds($allPoints, 500);

        // The max speed should reflect the fast chunk (above 18 km/h)
        $this->assertGreaterThan(18, $result['max_speed_kmh']);
        // But there should be multiple chunks
        $this->assertGreaterThanOrEqual(2, count($result['chunks']));
    }

    public function test_chunk_speed_train_returns_null_in_parser(): void
    {
        // Train has no speedCheckSegmentMeters (returns null)
        $this->assertNull(TransportMode::TRAIN->speedCheckSegmentMeters());
    }

    public function test_chunk_speed_bus_returns_null_in_parser(): void
    {
        $this->assertNull(TransportMode::BUS->speedCheckSegmentMeters());
    }

    public function test_chunk_speed_car_returns_null_in_parser(): void
    {
        $this->assertNull(TransportMode::CAR->speedCheckSegmentMeters());
    }

    // ==================== Segment Identification ====================

    public function test_single_mode_creates_one_segment(): void
    {
        $csv = $this->buildCsv([
            ['latitude' => 43.716667, 'longitude' => 10.400000, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 1000000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
            ['latitude' => 43.717000, 'longitude' => 10.400500, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 1010000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
            ['latitude' => 43.717500, 'longitude' => 10.401000, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 1020000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
        ]);

        $result = $this->parser->parse($csv);

        $this->assertCount(1, $result['segments']);
        $this->assertEquals(TransportMode::WALK, $result['segments'][0]['transport_mode']);
    }

    public function test_mode_change_creates_multiple_segments(): void
    {
        $csv = $this->buildCsv([
            // Walk segment
            ['latitude' => 43.716667, 'longitude' => 10.400000, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 1000000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
            ['latitude' => 43.717000, 'longitude' => 10.400500, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 1010000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
            // Bike segment starts
            ['latitude' => 43.717500, 'longitude' => 10.401000, 'accuracy' => 5.0, 'speed' => 5.0, 'timeStamp' => 1020000, 'vehicleMode' => 2, 'sessionId' => 'session-1'],
            ['latitude' => 43.718000, 'longitude' => 10.401500, 'accuracy' => 5.0, 'speed' => 5.0, 'timeStamp' => 1030000, 'vehicleMode' => 2, 'sessionId' => 'session-1'],
        ]);

        $result = $this->parser->parse($csv);

        $this->assertCount(2, $result['segments']);
        $this->assertEquals(TransportMode::WALK, $result['segments'][0]['transport_mode']);
        $this->assertEquals(TransportMode::BIKE, $result['segments'][1]['transport_mode']);
    }

    public function test_multimodal_detection(): void
    {
        $csv = $this->buildCsv([
            ['latitude' => 43.716667, 'longitude' => 10.400000, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 1000000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
            ['latitude' => 43.717000, 'longitude' => 10.400500, 'accuracy' => 5.0, 'speed' => 5.0, 'timeStamp' => 1010000, 'vehicleMode' => 2, 'sessionId' => 'session-1'],
            ['latitude' => 43.717500, 'longitude' => 10.401000, 'accuracy' => 5.0, 'speed' => 15.0, 'timeStamp' => 1020000, 'vehicleMode' => 3, 'sessionId' => 'session-1'],
        ]);

        $result = $this->parser->parse($csv);

        $this->assertTrue($result['summary']['is_multimodal']);
        $this->assertEquals(3, $result['summary']['segments_count']);
    }

    // ==================== Parse Method ====================

    public function test_parse_valid_csv(): void
    {
        $csv = $this->buildCsv([
            ['latitude' => 43.716667, 'longitude' => 10.400000, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 1000000, 'vehicleMode' => 1, 'sessionId' => 'session-abc'],
            ['latitude' => 43.717000, 'longitude' => 10.400500, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 1010000, 'vehicleMode' => 1, 'sessionId' => 'session-abc'],
        ]);

        $result = $this->parser->parse($csv);

        $this->assertArrayHasKey('session_id', $result);
        $this->assertArrayHasKey('points', $result);
        $this->assertArrayHasKey('segments', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertEquals('session-abc', $result['session_id']);
        $this->assertCount(2, $result['points']);
    }

    public function test_parse_missing_required_column_throws_exception(): void
    {
        // CSV missing 'latitude' column
        $csv = "longitude,accuracy,speed,timeStamp,vehicleMode,sessionId\n";
        $csv .= "10.4,5.0,1.5,1000000,1,session-1\n";

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Colonna richiesta mancante: latitude');

        $this->parser->parse($csv);
    }

    public function test_parse_empty_file_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->parser->parse("header\n");
    }

    public function test_parse_orders_points_by_timestamp(): void
    {
        $csv = $this->buildCsv([
            // Out of order timestamps
            ['latitude' => 43.717000, 'longitude' => 10.400500, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 2000000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
            ['latitude' => 43.716667, 'longitude' => 10.400000, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 1000000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
            ['latitude' => 43.717500, 'longitude' => 10.401000, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 3000000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
        ]);

        $result = $this->parser->parse($csv);

        $this->assertEquals(1000000, $result['points'][0]['timestamp']);
        $this->assertEquals(2000000, $result['points'][1]['timestamp']);
        $this->assertEquals(3000000, $result['points'][2]['timestamp']);
    }

    public function test_parse_skips_zero_coordinate_points(): void
    {
        $csv = $this->buildCsv([
            ['latitude' => 43.716667, 'longitude' => 10.400000, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 1000000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
            ['latitude' => 0, 'longitude' => 0, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 1010000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
            ['latitude' => 43.717000, 'longitude' => 10.400500, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 1020000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
        ]);

        $result = $this->parser->parse($csv);

        $this->assertCount(2, $result['points']);
    }

    public function test_parse_segment_metrics_calculated(): void
    {
        $csv = $this->buildCsv([
            ['latitude' => 43.716667, 'longitude' => 10.400000, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 1000000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
            ['latitude' => 43.717000, 'longitude' => 10.400500, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 1060000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
        ]);

        $result = $this->parser->parse($csv);
        $segment = $result['segments'][0];

        $this->assertArrayHasKey('distance_meters', $segment);
        $this->assertArrayHasKey('duration_seconds', $segment);
        $this->assertArrayHasKey('start_latitude', $segment);
        $this->assertArrayHasKey('end_latitude', $segment);
        $this->assertArrayHasKey('generates_credits', $segment);
        $this->assertGreaterThan(0, $segment['distance_meters']);
        $this->assertEquals(60, $segment['duration_seconds']);
    }

    public function test_parse_summary_fields(): void
    {
        $csv = $this->buildCsv([
            ['latitude' => 43.716667, 'longitude' => 10.400000, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 1000000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
            ['latitude' => 43.717000, 'longitude' => 10.400500, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 1060000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
        ]);

        $result = $this->parser->parse($csv);
        $summary = $result['summary'];

        $this->assertArrayHasKey('total_distance_meters', $summary);
        $this->assertArrayHasKey('valid_distance_meters', $summary);
        $this->assertArrayHasKey('is_multimodal', $summary);
        $this->assertArrayHasKey('primary_transport_mode', $summary);
        $this->assertArrayHasKey('points_count', $summary);
        $this->assertArrayHasKey('segments_count', $summary);
        $this->assertFalse($summary['is_multimodal']);
        $this->assertEquals(TransportMode::WALK, $summary['primary_transport_mode']);
    }

    public function test_parse_all_points_filtered_throws_exception(): void
    {
        // All points have bad accuracy
        $csv = $this->buildCsv([
            ['latitude' => 43.716667, 'longitude' => 10.400000, 'accuracy' => 50.0, 'speed' => 1.5, 'timeStamp' => 1000000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
            ['latitude' => 43.717000, 'longitude' => 10.400500, 'accuracy' => 50.0, 'speed' => 1.5, 'timeStamp' => 1010000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Nessun punto GPS valido dopo la pulizia dati');

        $this->parser->parse($csv);
    }

    public function test_parse_walk_generates_credits(): void
    {
        $csv = $this->buildCsv([
            ['latitude' => 43.716667, 'longitude' => 10.400000, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 1000000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
            ['latitude' => 43.717000, 'longitude' => 10.400500, 'accuracy' => 5.0, 'speed' => 1.5, 'timeStamp' => 1010000, 'vehicleMode' => 1, 'sessionId' => 'session-1'],
        ]);

        $result = $this->parser->parse($csv);
        $this->assertTrue($result['segments'][0]['generates_credits']);
    }

    public function test_parse_car_does_not_generate_credits(): void
    {
        $csv = $this->buildCsv([
            ['latitude' => 43.716667, 'longitude' => 10.400000, 'accuracy' => 5.0, 'speed' => 10.0, 'timeStamp' => 1000000, 'vehicleMode' => 5, 'sessionId' => 'session-1'],
            ['latitude' => 43.717000, 'longitude' => 10.400500, 'accuracy' => 5.0, 'speed' => 10.0, 'timeStamp' => 1010000, 'vehicleMode' => 5, 'sessionId' => 'session-1'],
        ]);

        $result = $this->parser->parse($csv);
        $this->assertFalse($result['segments'][0]['generates_credits']);
    }

    // ==================== Helper Methods ====================

    /**
     * Build a CSV string from an array of point data.
     */
    private function buildCsv(array $rows): string
    {
        $header = "latitude,longitude,accuracy,speed,timeStamp,vehicleMode,sessionId\n";
        $lines = [];

        foreach ($rows as $row) {
            $lines[] = implode(',', [
                $row['latitude'],
                $row['longitude'],
                $row['accuracy'],
                $row['speed'],
                $row['timeStamp'],
                $row['vehicleMode'],
                $row['sessionId'],
            ]);
        }

        return $header . implode("\n", $lines);
    }

    /**
     * Generate GPS points along a straight line going north.
     */
    private function generateLinearPoints(
        float $startLat,
        float $startLon,
        float $totalDistanceMeters,
        float $totalTimeMs,
        int $numPoints,
        float $startTimeMs = 0
    ): array {
        $points = [];
        // Roughly 1 degree latitude = 111,320 meters
        $latIncrement = ($totalDistanceMeters / 111320) / ($numPoints - 1);
        $timeIncrement = $totalTimeMs / ($numPoints - 1);

        for ($i = 0; $i < $numPoints; $i++) {
            $points[] = [
                'latitude' => $startLat + ($latIncrement * $i),
                'longitude' => $startLon,
                'accuracy' => 5.0,
                'speed' => $totalDistanceMeters / ($totalTimeMs / 1000),
                'timestamp' => (int) ($startTimeMs + ($timeIncrement * $i)),
                'vehicle_mode' => 1,
                'session_id' => 'test-session',
            ];
        }

        return $points;
    }
}
