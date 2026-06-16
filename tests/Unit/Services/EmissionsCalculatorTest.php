<?php

namespace Tests\Unit\Services;

use App\Enums\TransportMode;
use App\Models\TrackSegment;
use App\Services\EmissionsCalculator;
use PHPUnit\Framework\TestCase;

class EmissionsCalculatorTest extends TestCase
{
    private EmissionsCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new EmissionsCalculator();
    }

    private function makeSegment(TransportMode $mode, float $distanceMeters): TrackSegment
    {
        $segment = new TrackSegment();
        $segment->transport_mode = $mode;
        $segment->distance_meters = $distanceMeters;
        return $segment;
    }

    public function test_co2_saved_walking_10km(): void
    {
        $segment = $this->makeSegment(TransportMode::WALK, 10000);
        $co2 = $this->calculator->calculateCo2Saved($segment);

        // 10km * 140g/km (auto) - 10km * 0g/km (piedi) = 1400g
        $this->assertEquals(1400.0, $co2);
    }

    public function test_co2_saved_bike_5km(): void
    {
        $segment = $this->makeSegment(TransportMode::BIKE, 5000);
        $co2 = $this->calculator->calculateCo2Saved($segment);

        // 5km * 140g/km - 5km * 0g/km = 700g
        $this->assertEquals(700.0, $co2);
    }

    public function test_co2_saved_bus_10km(): void
    {
        $segment = $this->makeSegment(TransportMode::BUS, 10000);
        $co2 = $this->calculator->calculateCo2Saved($segment);

        // 10km * (140 - 50) = 900g
        $this->assertEquals(900.0, $co2);
    }

    public function test_co2_saved_train_10km(): void
    {
        $segment = $this->makeSegment(TransportMode::TRAIN, 10000);
        $co2 = $this->calculator->calculateCo2Saved($segment);

        // 10km * (140 - 14) = 1260g
        $this->assertEquals(1260.0, $co2);
    }

    public function test_co2_saved_car_is_zero(): void
    {
        $segment = $this->makeSegment(TransportMode::CAR, 10000);
        $co2 = $this->calculator->calculateCo2Saved($segment);

        // Auto vs auto = 0
        $this->assertEquals(0.0, $co2);
    }

    public function test_co2_saved_motorcycle_is_positive(): void
    {
        $segment = $this->makeSegment(TransportMode::MOTORCYCLE, 10000);
        $co2 = $this->calculator->calculateCo2Saved($segment);

        // 10km * (140 - 100) = 400g
        $this->assertEquals(400.0, $co2);
    }

    public function test_co2_saved_never_negative(): void
    {
        foreach (TransportMode::cases() as $mode) {
            $segment = $this->makeSegment($mode, 10000);
            $co2 = $this->calculator->calculateCo2Saved($segment);
            $this->assertGreaterThanOrEqual(0, $co2, "CO2 saved for {$mode->value} should not be negative");
        }
    }

    public function test_calories_walking_5km(): void
    {
        $segment = $this->makeSegment(TransportMode::WALK, 5000);
        $calories = $this->calculator->calculateCalories($segment);

        // 5km * 50 cal/km = 250
        $this->assertEquals(250.0, $calories);
    }

    public function test_calories_bike_10km(): void
    {
        $segment = $this->makeSegment(TransportMode::BIKE, 10000);
        $calories = $this->calculator->calculateCalories($segment);

        // 10km * 25 cal/km = 250
        $this->assertEquals(250.0, $calories);
    }

    public function test_calories_car_is_zero(): void
    {
        $segment = $this->makeSegment(TransportMode::CAR, 10000);
        $calories = $this->calculator->calculateCalories($segment);

        $this->assertEquals(0.0, $calories);
    }

    public function test_money_saved_walking_10km(): void
    {
        $segment = $this->makeSegment(TransportMode::WALK, 10000);
        $saved = $this->calculator->calculateMoneySaved($segment);

        // Costo auto equivalente: 10 km * (0.11 + 0.106 + 0.11) = 3.26 €/km
        // Piedi: 0 costo → risparmio = 3.26
        $this->assertEqualsWithDelta(3.26, $saved, 0.01);
    }

    public function test_money_saved_car_is_zero(): void
    {
        $segment = $this->makeSegment(TransportMode::CAR, 10000);
        $saved = $this->calculator->calculateMoneySaved($segment);

        $this->assertEquals(0.0, $saved);
    }

    public function test_all_emissions_saved_returns_all_keys(): void
    {
        $segment = $this->makeSegment(TransportMode::BIKE, 5000);
        $emissions = $this->calculator->calculateAllEmissionsSaved($segment);

        $this->assertArrayHasKey('co2', $emissions);
        $this->assertArrayHasKey('so2', $emissions);
        $this->assertArrayHasKey('nox', $emissions);
        $this->assertArrayHasKey('co', $emissions);
        $this->assertArrayHasKey('pm10', $emissions);
    }

    public function test_calculate_all_returns_all_metrics(): void
    {
        $segment = $this->makeSegment(TransportMode::WALK, 1000);
        $all = $this->calculator->calculateAll($segment);

        // Emissioni risparmiate
        $this->assertArrayHasKey('co2_saved_grams', $all);
        $this->assertArrayHasKey('so2_saved_mg', $all);
        $this->assertArrayHasKey('nox_saved_grams', $all);
        $this->assertArrayHasKey('co_saved_grams', $all);
        $this->assertArrayHasKey('pm10_saved_grams', $all);
        // Emissioni effettive
        $this->assertArrayHasKey('co2_emitted_grams', $all);
        $this->assertArrayHasKey('so2_emitted_mg', $all);
        $this->assertArrayHasKey('nox_emitted_grams', $all);
        $this->assertArrayHasKey('co_emitted_grams', $all);
        $this->assertArrayHasKey('pm10_emitted_grams', $all);
        // Calorie e costi
        $this->assertArrayHasKey('calories_burned', $all);
        $this->assertArrayHasKey('money_saved_euros', $all);
        $this->assertArrayHasKey('cost_fuel_euros', $all);
        $this->assertArrayHasKey('cost_depreciation_euros', $all);
        $this->assertArrayHasKey('cost_operation_euros', $all);
        $this->assertArrayHasKey('cost_time_euros', $all);
        $this->assertArrayHasKey('cost_total_euros', $all);
    }

    public function test_co2_equivalents(): void
    {
        // 20kg CO2 = 1 albero/anno
        $equivalents = $this->calculator->getCo2Equivalents(20000); // 20kg in grammi
        $this->assertEquals(1.0, $equivalents['trees_year']);
    }

    public function test_calories_equivalents(): void
    {
        $equivalents = $this->calculator->getCaloriesEquivalents(800);
        $this->assertEquals(1.0, $equivalents['pizzas']);
    }

    public function test_zero_distance_returns_zero(): void
    {
        $segment = $this->makeSegment(TransportMode::WALK, 0);

        $this->assertEquals(0.0, $this->calculator->calculateCo2Saved($segment));
        $this->assertEquals(0.0, $this->calculator->calculateCalories($segment));
        $this->assertEquals(0.0, $this->calculator->calculateMoneySaved($segment));
    }

    // ==================== Transport Costs ====================

    public function test_transport_cost_fuel_calculation(): void
    {
        // 10 km distance
        $segment = $this->makeSegmentWithDuration(TransportMode::WALK, 10000, 3600);
        $costs = $this->calculator->calculateTransportCosts($segment);

        // fuel = 10 km * 0.11 = 1.10
        $this->assertEqualsWithDelta(1.10, $costs['fuel'], 0.001);
    }

    public function test_transport_cost_depreciation_calculation(): void
    {
        $segment = $this->makeSegmentWithDuration(TransportMode::WALK, 10000, 3600);
        $costs = $this->calculator->calculateTransportCosts($segment);

        // depreciation = 10 km * 0.106 = 1.06
        $this->assertEqualsWithDelta(1.06, $costs['depreciation'], 0.001);
    }

    public function test_transport_cost_operation_calculation(): void
    {
        $segment = $this->makeSegmentWithDuration(TransportMode::WALK, 10000, 3600);
        $costs = $this->calculator->calculateTransportCosts($segment);

        // operation = 10 km * 0.11 = 1.10
        $this->assertEqualsWithDelta(1.10, $costs['operation'], 0.001);
    }

    public function test_transport_cost_time_calculation(): void
    {
        // 1 hour duration
        $segment = $this->makeSegmentWithDuration(TransportMode::WALK, 10000, 3600);
        $costs = $this->calculator->calculateTransportCosts($segment);

        // time = 1 hour * 8.0 EUR/h = 8.0
        $this->assertEqualsWithDelta(8.0, $costs['time'], 0.001);
    }

    public function test_transport_cost_total_is_sum_of_components(): void
    {
        // 10 km, 1 hour
        $segment = $this->makeSegmentWithDuration(TransportMode::WALK, 10000, 3600);
        $costs = $this->calculator->calculateTransportCosts($segment);

        // total = fuel + depreciation + operation + time
        // = 1.10 + 1.06 + 1.10 + 8.0 = 11.26
        $expectedTotal = $costs['fuel'] + $costs['depreciation'] + $costs['operation'] + $costs['time'];
        $this->assertEqualsWithDelta($expectedTotal, $costs['total'], 0.001);
        $this->assertEqualsWithDelta(11.26, $costs['total'], 0.01);
    }

    public function test_transport_cost_concrete_example_10km_1hour(): void
    {
        $segment = $this->makeSegmentWithDuration(TransportMode::BIKE, 10000, 3600);
        $costs = $this->calculator->calculateTransportCosts($segment);

        $this->assertEqualsWithDelta(1.10, $costs['fuel'], 0.001);
        $this->assertEqualsWithDelta(1.06, $costs['depreciation'], 0.001);
        $this->assertEqualsWithDelta(1.10, $costs['operation'], 0.001);
        $this->assertEqualsWithDelta(8.0, $costs['time'], 0.001);
        $this->assertEqualsWithDelta(11.26, $costs['total'], 0.01);
    }

    public function test_transport_cost_zero_duration(): void
    {
        $segment = $this->makeSegmentWithDuration(TransportMode::WALK, 5000, 0);
        $costs = $this->calculator->calculateTransportCosts($segment);

        // Time cost = 0 since duration is 0
        $this->assertEquals(0, $costs['time']);
        // Distance costs still apply
        $this->assertGreaterThan(0, $costs['fuel']);
    }

    // ==================== Actual Emissions ====================

    public function test_walking_emits_zero_for_all_pollutants(): void
    {
        $segment = $this->makeSegment(TransportMode::WALK, 10000);
        $emissions = $this->calculator->calculateEmissions($segment);

        $this->assertEquals(0.0, $emissions['co2']);
        $this->assertEquals(0.0, $emissions['so2']);
        $this->assertEquals(0.0, $emissions['nox']);
        $this->assertEquals(0.0, $emissions['co']);
        $this->assertEquals(0.0, $emissions['pm10']);
    }

    public function test_bike_emits_zero_for_all_pollutants(): void
    {
        $segment = $this->makeSegment(TransportMode::BIKE, 10000);
        $emissions = $this->calculator->calculateEmissions($segment);

        $this->assertEquals(0.0, $emissions['co2']);
        $this->assertEquals(0.0, $emissions['so2']);
        $this->assertEquals(0.0, $emissions['nox']);
        $this->assertEquals(0.0, $emissions['co']);
        $this->assertEquals(0.0, $emissions['pm10']);
    }

    public function test_car_emits_correct_co2(): void
    {
        $segment = $this->makeSegment(TransportMode::CAR, 10000); // 10 km
        $emissions = $this->calculator->calculateEmissions($segment);

        // 10 km * 140 g/km = 1400 g
        $this->assertEqualsWithDelta(1400.0, $emissions['co2'], 0.01);
    }

    public function test_car_emits_correct_so2(): void
    {
        $segment = $this->makeSegment(TransportMode::CAR, 10000);
        $emissions = $this->calculator->calculateEmissions($segment);

        // 10 km * 0.8 mg/km = 8.0 mg
        $this->assertEqualsWithDelta(8.0, $emissions['so2'], 0.01);
    }

    public function test_car_emits_correct_nox(): void
    {
        $segment = $this->makeSegment(TransportMode::CAR, 10000);
        $emissions = $this->calculator->calculateEmissions($segment);

        // 10 km * 0.25 g/km = 2.5 g
        $this->assertEqualsWithDelta(2.5, $emissions['nox'], 0.01);
    }

    public function test_car_emits_correct_pm10(): void
    {
        $segment = $this->makeSegment(TransportMode::CAR, 10000);
        $emissions = $this->calculator->calculateEmissions($segment);

        // 10 km * 0.10 g/km = 1.0 g
        $this->assertEqualsWithDelta(1.0, $emissions['pm10'], 0.01);
    }

    public function test_bus_emits_correct_co2(): void
    {
        $segment = $this->makeSegment(TransportMode::BUS, 10000);
        $emissions = $this->calculator->calculateEmissions($segment);

        // 10 km * 50 g/km = 500 g
        $this->assertEqualsWithDelta(500.0, $emissions['co2'], 0.01);
    }

    public function test_train_has_nonzero_pm10(): void
    {
        $segment = $this->makeSegment(TransportMode::TRAIN, 10000);
        $emissions = $this->calculator->calculateEmissions($segment);

        // 10 km * 0.0002 g/km = 0.002 g
        $this->assertEqualsWithDelta(0.002, $emissions['pm10'], 0.0001);
        $this->assertGreaterThan(0, $emissions['pm10']);
    }

    public function test_train_emits_correct_co2(): void
    {
        $segment = $this->makeSegment(TransportMode::TRAIN, 10000);
        $emissions = $this->calculator->calculateEmissions($segment);

        // 10 km * 14 g/km = 140 g
        $this->assertEqualsWithDelta(140.0, $emissions['co2'], 0.01);
    }

    public function test_train_zero_so2_nox_co(): void
    {
        $segment = $this->makeSegment(TransportMode::TRAIN, 10000);
        $emissions = $this->calculator->calculateEmissions($segment);

        $this->assertEquals(0.0, $emissions['so2']);
        $this->assertEquals(0.0, $emissions['nox']);
        $this->assertEquals(0.0, $emissions['co']);
    }

    public function test_motorcycle_emits_correct_co2(): void
    {
        $segment = $this->makeSegment(TransportMode::MOTORCYCLE, 10000);
        $emissions = $this->calculator->calculateEmissions($segment);

        // 10 km * 100 g/km = 1000 g
        $this->assertEqualsWithDelta(1000.0, $emissions['co2'], 0.01);
    }

    // ==================== Aggregated Calculation ====================

    public function test_aggregated_calculation_multiple_segments(): void
    {
        $segments = [
            $this->makeSegmentWithDuration(TransportMode::WALK, 5000, 3000),  // 5 km
            $this->makeSegmentWithDuration(TransportMode::BIKE, 10000, 1800), // 10 km
        ];

        $totals = $this->calculator->calculateAggregated($segments);

        // CO2 saved: walk 5km*(140-0)=700 + bike 10km*(140-0)=1400 = 2100
        $this->assertEqualsWithDelta(2100.0, $totals['co2_saved_grams'], 0.01);

        // CO2 emitted: walk 0 + bike 0 = 0
        $this->assertEquals(0.0, $totals['co2_emitted_grams']);

        // Calories: walk 5*50=250 + bike 10*25=250 = 500
        $this->assertEqualsWithDelta(500.0, $totals['calories_burned'], 0.01);

        // Costs should aggregate too
        $this->assertGreaterThan(0, $totals['cost_total_euros']);
    }

    public function test_aggregated_mixed_modes_with_car(): void
    {
        $segments = [
            $this->makeSegmentWithDuration(TransportMode::WALK, 3000, 1800),
            $this->makeSegmentWithDuration(TransportMode::CAR, 10000, 600),
        ];

        $totals = $this->calculator->calculateAggregated($segments);

        // Car emits CO2, walk does not
        $this->assertGreaterThan(0, $totals['co2_emitted_grams']);
        // Walk saves CO2 vs car, car saves 0
        $this->assertGreaterThan(0, $totals['co2_saved_grams']);
    }

    // ==================== Helper for segments with duration ====================

    private function makeSegmentWithDuration(TransportMode $mode, float $distanceMeters, int $durationSeconds): TrackSegment
    {
        $segment = new TrackSegment();
        $segment->transport_mode = $mode;
        $segment->distance_meters = $distanceMeters;
        $segment->duration_seconds = $durationSeconds;
        return $segment;
    }
}
