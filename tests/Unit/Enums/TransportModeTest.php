<?php

namespace Tests\Unit\Enums;

use App\Enums\TransportMode;
use PHPUnit\Framework\TestCase;

class TransportModeTest extends TestCase
{
    public function test_all_transport_modes_exist(): void
    {
        $this->assertCount(6, TransportMode::cases());
    }

    public function test_credit_generating_modes(): void
    {
        $this->assertTrue(TransportMode::TRAIN->generatesCredits());
        $this->assertTrue(TransportMode::BUS->generatesCredits());
        $this->assertTrue(TransportMode::BIKE->generatesCredits());
        $this->assertTrue(TransportMode::WALK->generatesCredits());
        $this->assertFalse(TransportMode::CAR->generatesCredits());
        $this->assertFalse(TransportMode::MOTORCYCLE->generatesCredits());
    }

    public function test_co2_per_km_values(): void
    {
        $this->assertEquals(140.0, TransportMode::CAR->co2PerKm());
        $this->assertEquals(50.0, TransportMode::BUS->co2PerKm());
        $this->assertEquals(14.0, TransportMode::TRAIN->co2PerKm());
        $this->assertEquals(100.0, TransportMode::MOTORCYCLE->co2PerKm());
        $this->assertEquals(0.0, TransportMode::BIKE->co2PerKm());
        $this->assertEquals(0.0, TransportMode::WALK->co2PerKm());
    }

    public function test_calories_per_km(): void
    {
        $this->assertEquals(25.0, TransportMode::BIKE->caloriesPerKm());
        $this->assertEquals(50.0, TransportMode::WALK->caloriesPerKm());
        $this->assertEquals(0.0, TransportMode::CAR->caloriesPerKm());
        $this->assertEquals(0.0, TransportMode::TRAIN->caloriesPerKm());
    }

    public function test_max_speed_limits(): void
    {
        $this->assertEquals(18.0, TransportMode::WALK->maxSpeedKmh());
        $this->assertEquals(40.0, TransportMode::BIKE->maxSpeedKmh());
        $this->assertNull(TransportMode::CAR->maxSpeedKmh());
        $this->assertNull(TransportMode::TRAIN->maxSpeedKmh());
    }

    public function test_speed_check_segment_meters(): void
    {
        $this->assertEquals(500, TransportMode::WALK->speedCheckSegmentMeters());
        $this->assertEquals(800, TransportMode::BIKE->speedCheckSegmentMeters());
        $this->assertNull(TransportMode::CAR->speedCheckSegmentMeters());
    }

    public function test_vehicle_mode_conversion(): void
    {
        $this->assertEquals(TransportMode::WALK, TransportMode::fromVehicleMode(0));
        $this->assertEquals(TransportMode::WALK, TransportMode::fromVehicleMode(1));
        $this->assertEquals(TransportMode::BIKE, TransportMode::fromVehicleMode(2));
        $this->assertEquals(TransportMode::TRAIN, TransportMode::fromVehicleMode(3));
        $this->assertEquals(TransportMode::BUS, TransportMode::fromVehicleMode(4));
        $this->assertEquals(TransportMode::CAR, TransportMode::fromVehicleMode(5));
        $this->assertEquals(TransportMode::MOTORCYCLE, TransportMode::fromVehicleMode(6));
    }

    public function test_vehicle_mode_roundtrip(): void
    {
        foreach (TransportMode::cases() as $mode) {
            $vehicleMode = $mode->toVehicleMode();
            $this->assertEquals($mode, TransportMode::fromVehicleMode($vehicleMode));
        }
    }

    public function test_unknown_vehicle_mode_defaults_to_walk(): void
    {
        $this->assertEquals(TransportMode::WALK, TransportMode::fromVehicleMode(99));
        $this->assertEquals(TransportMode::WALK, TransportMode::fromVehicleMode(-1));
    }

    public function test_from_track_value_accepts_numeric_codes(): void
    {
        $this->assertEquals(TransportMode::WALK, TransportMode::fromTrackValue('0'));
        $this->assertEquals(TransportMode::WALK, TransportMode::fromTrackValue('1'));
        $this->assertEquals(TransportMode::BIKE, TransportMode::fromTrackValue('2'));
        $this->assertEquals(TransportMode::TRAIN, TransportMode::fromTrackValue('3'));
        $this->assertEquals(TransportMode::BUS, TransportMode::fromTrackValue('4'));
        $this->assertEquals(TransportMode::CAR, TransportMode::fromTrackValue('5'));
        $this->assertEquals(TransportMode::MOTORCYCLE, TransportMode::fromTrackValue('6'));
    }

    public function test_from_track_value_accepts_textual_labels(): void
    {
        // Caso reale dell'app: invia "bike" invece del codice 2
        $this->assertEquals(TransportMode::BIKE, TransportMode::fromTrackValue('bike'));
        $this->assertEquals(TransportMode::BIKE, TransportMode::fromTrackValue('BIKE'));
        $this->assertEquals(TransportMode::BIKE, TransportMode::fromTrackValue('bicycle'));
        $this->assertEquals(TransportMode::BIKE, TransportMode::fromTrackValue('scooter'));
        $this->assertEquals(TransportMode::WALK, TransportMode::fromTrackValue('walk'));
        $this->assertEquals(TransportMode::TRAIN, TransportMode::fromTrackValue('train'));
        $this->assertEquals(TransportMode::BUS, TransportMode::fromTrackValue('bus'));
        $this->assertEquals(TransportMode::CAR, TransportMode::fromTrackValue('car'));
        $this->assertEquals(TransportMode::MOTORCYCLE, TransportMode::fromTrackValue('motorcycle'));
    }

    public function test_from_track_value_trims_whitespace(): void
    {
        $this->assertEquals(TransportMode::BIKE, TransportMode::fromTrackValue(' 2 '));
        $this->assertEquals(TransportMode::BIKE, TransportMode::fromTrackValue(' bike '));
    }

    public function test_from_track_value_returns_null_for_unrecognized(): void
    {
        $this->assertNull(TransportMode::fromTrackValue(''));
        $this->assertNull(TransportMode::fromTrackValue('   '));
        $this->assertNull(TransportMode::fromTrackValue('99'));
        $this->assertNull(TransportMode::fromTrackValue('-1'));
        $this->assertNull(TransportMode::fromTrackValue('foobar'));
    }

    public function test_credit_generating_static_method(): void
    {
        $creditModes = TransportMode::creditGenerating();
        $this->assertCount(4, $creditModes);
    }

    public function test_all_emissions_are_non_negative(): void
    {
        foreach (TransportMode::cases() as $mode) {
            $this->assertGreaterThanOrEqual(0, $mode->co2PerKm());
            $this->assertGreaterThanOrEqual(0, $mode->so2PerKm());
            $this->assertGreaterThanOrEqual(0, $mode->noxPerKm());
            $this->assertGreaterThanOrEqual(0, $mode->coPerKm());
            $this->assertGreaterThanOrEqual(0, $mode->pm10PerKm());
            $this->assertGreaterThanOrEqual(0, $mode->caloriesPerKm());
        }
    }
}
