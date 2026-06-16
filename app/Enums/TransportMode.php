<?php

namespace App\Enums;

enum TransportMode: string
{
    case TRAIN = 'train';
    case BUS = 'bus';
    case CAR = 'car';
    case MOTORCYCLE = 'motorcycle';
    case BIKE = 'bike';
    case WALK = 'walk';

    public function label(): string
    {
        return match ($this) {
            self::TRAIN => 'Treno',
            self::BUS => 'Autobus',
            self::CAR => 'Auto',
            self::MOTORCYCLE => 'Motociclo',
            self::BIKE => 'Bici/Monopattino',
            self::WALK => 'Piedi',
        };
    }

    /**
     * Indica se la modalità genera crediti
     */
    public function generatesCredits(): bool
    {
        return match ($this) {
            self::TRAIN, self::BUS, self::BIKE, self::WALK => true,
            self::CAR, self::MOTORCYCLE => false,
        };
    }

    /**
     * Emissioni CO2 in grammi per km
     */
    public function co2PerKm(): float
    {
        return match ($this) {
            self::CAR => 140.0,
            self::BUS => 50.0,
            self::TRAIN => 14.0,
            self::MOTORCYCLE => 100.0,
            self::BIKE, self::WALK => 0.0,
        };
    }

    /**
     * Emissioni SO2 in mg per km
     */
    public function so2PerKm(): float
    {
        return match ($this) {
            self::CAR => 0.8,
            self::BUS => 0.196,
            self::MOTORCYCLE => 0.45,
            self::TRAIN, self::BIKE, self::WALK => 0.0,
        };
    }

    /**
     * Emissioni NOx in grammi per km
     */
    public function noxPerKm(): float
    {
        return match ($this) {
            self::CAR => 0.25,
            self::BUS => 0.000034,
            self::MOTORCYCLE => 0.00008,
            self::TRAIN, self::BIKE, self::WALK => 0.0,
        };
    }

    /**
     * Emissioni CO in grammi per km
     */
    public function coPerKm(): float
    {
        return match ($this) {
            self::CAR => 0.4,
            self::BUS => 0.12,
            self::MOTORCYCLE => 0.00002,
            self::TRAIN, self::BIKE, self::WALK => 0.0,
        };
    }

    /**
     * Emissioni PM10 in grammi per km
     */
    public function pm10PerKm(): float
    {
        return match ($this) {
            self::CAR => 0.10,
            self::BUS => 0.03,
            self::TRAIN => 0.0002,
            self::MOTORCYCLE => 0.02,
            self::BIKE, self::WALK => 0.0,
        };
    }

    /**
     * Calorie bruciate per km
     */
    public function caloriesPerKm(): float
    {
        return match ($this) {
            self::BIKE => 25.0,
            self::WALK => 50.0,
            self::TRAIN, self::BUS, self::CAR, self::MOTORCYCLE => 0.0,
        };
    }

    /**
     * Velocità massima consentita in km/h per validazione
     */
    public function maxSpeedKmh(): ?float
    {
        return match ($this) {
            self::WALK => 18.0,
            self::BIKE => 40.0,
            default => null,
        };
    }

    /**
     * Lunghezza segmento per controllo velocità in metri
     */
    public function speedCheckSegmentMeters(): ?int
    {
        return match ($this) {
            self::WALK => 500,
            self::BIKE => 800,
            default => null,
        };
    }

    /**
     * Nome API (usato nelle risposte REST)
     */
    public function apiName(): string
    {
        return match ($this) {
            self::WALK => 'walk',
            self::BIKE => 'bicycle',
            self::TRAIN, self::BUS => 'public_transport',
            self::CAR => 'car',
            self::MOTORCYCLE => 'motorcycle',
        };
    }

    /**
     * Converte un nome API nel TransportMode corrispondente
     */
    public static function fromApiName(string $apiName): ?self
    {
        return match ($apiName) {
            'walk' => self::WALK,
            'bicycle', 'scooter' => self::BIKE,
            'public_transport', 'public' => self::BUS, // default a bus per TPL generico
            'car' => self::CAR,
            'motorcycle' => self::MOTORCYCLE,
            default => null,
        };
    }

    /**
     * Nomi API disponibili per i filtri
     */
    public static function apiNames(): array
    {
        return ['walk', 'bicycle', 'scooter', 'public_transport', 'car', 'motorcycle'];
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Modalità che generano crediti
     */
    public static function creditGenerating(): array
    {
        return array_filter(self::cases(), fn($mode) => $mode->generatesCredits());
    }

    /**
     * Converte il vehicleMode intero dal file traccia alla modalità
     * Mapping basato sui dati dell'app mobile:
     * 0 = A piedi (default/unknown)
     * 1 = A piedi
     * 2 = Bici
     * 3 = Treno
     * 4 = Bus
     * 5 = Auto
     * 6 = Moto
     */
    public static function fromVehicleMode(int $vehicleMode): self
    {
        return match ($vehicleMode) {
            0, 1 => self::WALK,
            2 => self::BIKE,
            3 => self::TRAIN,
            4 => self::BUS,
            5 => self::CAR,
            6 => self::MOTORCYCLE,
            default => self::WALK, // fallback
        };
    }

    /**
     * Converte il valore grezzo della colonna vehicleMode del file traccia.
     *
     * Accetta sia i codici numerici (0-6) sia le label testuali (es. "bike",
     * "walk") che alcune versioni dell'app mobile inviano al posto del numero.
     * Restituisce null se il valore non è riconosciuto, così il chiamante può
     * loggarlo e applicare un fallback invece di declassarlo silenziosamente.
     */
    public static function fromTrackValue(string $value): ?self
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        // Codice numerico (es. "2")
        if (is_numeric($value)) {
            return match ((int) $value) {
                0, 1 => self::WALK,
                2 => self::BIKE,
                3 => self::TRAIN,
                4 => self::BUS,
                5 => self::CAR,
                6 => self::MOTORCYCLE,
                default => null,
            };
        }

        // Label testuale (case-insensitive) + sinonimi accettati
        return match (strtolower($value)) {
            'walk', 'foot', 'piedi' => self::WALK,
            'bike', 'bicycle', 'scooter', 'monopattino', 'bici' => self::BIKE,
            'train', 'treno' => self::TRAIN,
            'bus', 'autobus' => self::BUS,
            'car', 'auto' => self::CAR,
            'motorcycle', 'moto' => self::MOTORCYCLE,
            default => null,
        };
    }

    /**
     * Restituisce il vehicleMode intero per questa modalità
     */
    public function toVehicleMode(): int
    {
        return match ($this) {
            self::WALK => 1,
            self::BIKE => 2,
            self::TRAIN => 3,
            self::BUS => 4,
            self::CAR => 5,
            self::MOTORCYCLE => 6,
        };
    }
}
