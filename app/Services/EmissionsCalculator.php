<?php

namespace App\Services;

use App\Enums\TransportMode;
use App\Models\TrackSegment;

/**
 * Calcola emissioni (effettive e risparmiate), calorie e costi di trasporto.
 *
 * Valori da Algoritmo.pdf (v1.0 - 9 ottobre 2025)
 */
class EmissionsCalculator
{
    // === Costi di trasporto (da Algoritmo.pdf pag. 7) ===

    /** Costo medio carburante €/km (solo auto e motociclo) */
    protected const COST_FUEL_PER_KM = 0.11;

    /** Costo ammortamento €/km (solo auto e motociclo) */
    protected const COST_DEPRECIATION_PER_KM = 0.106;

    /** Costo esercizio €/km (solo auto e motociclo) */
    protected const COST_OPERATION_PER_KM = 0.11;

    /** Costo tempo spostamento €/h (tutte le tipologie) */
    protected const COST_TIME_PER_HOUR = 8.0;

    // === Emissioni effettive ===

    /**
     * Calcola le emissioni effettive per un segmento (quanto ha emesso la modalità usata)
     *
     * @return array{co2: float, so2: float, nox: float, co: float, pm10: float}
     */
    public function calculateEmissions(TrackSegment $segment): array
    {
        $distanceKm = $segment->distance_meters / 1000;
        $mode = $segment->transport_mode;

        return [
            'co2' => $mode->co2PerKm() * $distanceKm,
            'so2' => $mode->so2PerKm() * $distanceKm,
            'nox' => $mode->noxPerKm() * $distanceKm,
            'co' => $mode->coPerKm() * $distanceKm,
            'pm10' => $mode->pm10PerKm() * $distanceKm,
        ];
    }

    // === Emissioni risparmiate ===

    /**
     * Calcola CO2 risparmiata rispetto all'uso dell'auto
     */
    public function calculateCo2Saved(TrackSegment $segment): float
    {
        $distanceKm = $segment->distance_meters / 1000;

        $carCo2 = TransportMode::CAR->co2PerKm() * $distanceKm;
        $modeCo2 = $segment->transport_mode->co2PerKm() * $distanceKm;

        return max(0, $carCo2 - $modeCo2);
    }

    /**
     * Calcola tutte le emissioni risparmiate (differenza vs auto)
     *
     * @return array{co2: float, so2: float, nox: float, co: float, pm10: float}
     */
    public function calculateAllEmissionsSaved(TrackSegment $segment): array
    {
        $distanceKm = $segment->distance_meters / 1000;

        return [
            'co2' => max(0, (TransportMode::CAR->co2PerKm() - $segment->transport_mode->co2PerKm()) * $distanceKm),
            'so2' => max(0, (TransportMode::CAR->so2PerKm() - $segment->transport_mode->so2PerKm()) * $distanceKm),
            'nox' => max(0, (TransportMode::CAR->noxPerKm() - $segment->transport_mode->noxPerKm()) * $distanceKm),
            'co' => max(0, (TransportMode::CAR->coPerKm() - $segment->transport_mode->coPerKm()) * $distanceKm),
            'pm10' => max(0, (TransportMode::CAR->pm10PerKm() - $segment->transport_mode->pm10PerKm()) * $distanceKm),
        ];
    }

    // === Calorie ===

    /**
     * Calcola le calorie bruciate
     */
    public function calculateCalories(TrackSegment $segment): float
    {
        $distanceKm = $segment->distance_meters / 1000;

        return $segment->transport_mode->caloriesPerKm() * $distanceKm;
    }

    // === Costi di trasporto (da Algoritmo.pdf) ===

    /**
     * Calcola i costi di trasporto equivalenti (auto) per un segmento.
     *
     * Da Algoritmo.pdf:
     * - Carburante: 0.11 €/km (solo auto/moto)
     * - Ammortamento: 0.106 €/km (solo auto/moto)
     * - Esercizio: 0.11 €/km (solo auto/moto)
     * - Tempo: 8 €/h (tutte le tipologie)
     *
     * @return array{fuel: float, depreciation: float, operation: float, time: float, total: float}
     */
    public function calculateTransportCosts(TrackSegment $segment): array
    {
        $distanceKm = $segment->distance_meters / 1000;
        $durationHours = ($segment->duration_seconds ?? 0) / 3600;

        // Costi carburante, ammortamento ed esercizio sono calcolati come
        // "equivalente auto" per la stessa distanza
        $fuel = $distanceKm * self::COST_FUEL_PER_KM;
        $depreciation = $distanceKm * self::COST_DEPRECIATION_PER_KM;
        $operation = $distanceKm * self::COST_OPERATION_PER_KM;

        // Costo tempo si applica a tutte le tipologie
        $time = $durationHours * self::COST_TIME_PER_HOUR;

        $total = $fuel + $depreciation + $operation + $time;

        return [
            'fuel' => round($fuel, 4),
            'depreciation' => round($depreciation, 4),
            'operation' => round($operation, 4),
            'time' => round($time, 4),
            'total' => round($total, 4),
        ];
    }

    /**
     * Calcola il risparmio economico rispetto all'auto
     */
    public function calculateMoneySaved(TrackSegment $segment): float
    {
        $distanceKm = $segment->distance_meters / 1000;

        // Costo auto per la stessa distanza (carburante + ammortamento + esercizio)
        $carCostPerKm = self::COST_FUEL_PER_KM + self::COST_DEPRECIATION_PER_KM + self::COST_OPERATION_PER_KM;

        return match ($segment->transport_mode) {
            TransportMode::WALK, TransportMode::BIKE => $carCostPerKm * $distanceKm,
            TransportMode::BUS, TransportMode::TRAIN => $carCostPerKm * $distanceKm, // TPL ha costo ma non lo sottraiamo
            TransportMode::CAR, TransportMode::MOTORCYCLE => 0,
        };
    }

    // === Calcolo completo ===

    /**
     * Calcola tutte le metriche per un segmento
     */
    public function calculateAll(TrackSegment $segment): array
    {
        $emissionsActual = $this->calculateEmissions($segment);
        $emissionsSaved = $this->calculateAllEmissionsSaved($segment);
        $costs = $this->calculateTransportCosts($segment);

        return [
            // Emissioni effettive
            'co2_emitted_grams' => $emissionsActual['co2'],
            'so2_emitted_mg' => $emissionsActual['so2'],
            'nox_emitted_grams' => $emissionsActual['nox'],
            'co_emitted_grams' => $emissionsActual['co'],
            'pm10_emitted_grams' => $emissionsActual['pm10'],

            // Emissioni risparmiate
            'co2_saved_grams' => $emissionsSaved['co2'],
            'so2_saved_mg' => $emissionsSaved['so2'],
            'nox_saved_grams' => $emissionsSaved['nox'],
            'co_saved_grams' => $emissionsSaved['co'],
            'pm10_saved_grams' => $emissionsSaved['pm10'],

            // Calorie
            'calories_burned' => $this->calculateCalories($segment),

            // Costi trasporto
            'cost_fuel_euros' => $costs['fuel'],
            'cost_depreciation_euros' => $costs['depreciation'],
            'cost_operation_euros' => $costs['operation'],
            'cost_time_euros' => $costs['time'],
            'cost_total_euros' => $costs['total'],

            // Risparmio
            'money_saved_euros' => $this->calculateMoneySaved($segment),
        ];
    }

    /**
     * Calcola le metriche aggregate per più segmenti
     */
    public function calculateAggregated(iterable $segments): array
    {
        $keys = [
            'co2_emitted_grams', 'so2_emitted_mg', 'nox_emitted_grams',
            'co_emitted_grams', 'pm10_emitted_grams',
            'co2_saved_grams', 'so2_saved_mg', 'nox_saved_grams',
            'co_saved_grams', 'pm10_saved_grams',
            'calories_burned', 'money_saved_euros',
            'cost_fuel_euros', 'cost_depreciation_euros', 'cost_operation_euros',
            'cost_time_euros', 'cost_total_euros',
        ];

        $totals = array_fill_keys($keys, 0);

        foreach ($segments as $segment) {
            $metrics = $this->calculateAll($segment);
            foreach ($totals as $key => &$value) {
                $value += $metrics[$key] ?? 0;
            }
        }

        return $totals;
    }

    /**
     * Restituisce equivalenze comprensibili per la CO2 risparmiata
     */
    public function getCo2Equivalents(float $co2Grams): array
    {
        $co2Kg = $co2Grams / 1000;

        return [
            'trees_year' => round($co2Kg / 20, 2),
            'showers' => round($co2Kg / 0.5, 1),
            'car_km_avoided' => round($co2Grams / 140, 1),
            'smartphone_charges' => round($co2Grams / 8, 0),
        ];
    }

    /**
     * Restituisce equivalenze comprensibili per le calorie bruciate
     */
    public function getCaloriesEquivalents(float $calories): array
    {
        return [
            'apples' => round($calories / 50, 1),
            'pizzas' => round($calories / 800, 2),
            'cappuccinos' => round($calories / 120, 1),
            'running_minutes' => round($calories / 10, 0),
        ];
    }
}
