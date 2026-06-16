<?php

namespace App\Services;

use App\Enums\TransportMode;
use App\Models\Badge;
use App\Models\Track;
use App\Models\TrackSegment;
use App\Models\User;
use App\Notifications\BadgeEarnedNotification;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BadgeService
{
    /**
     * Controlla e assegna tutti i badge applicabili all'utente.
     * Chiamato dopo la validazione di una traccia.
     *
     * @return Collection<Badge> Badge appena assegnati
     */
    public function checkAndAward(User $user): Collection
    {
        $awarded = collect();

        $activeBadges = Badge::active()->ordered()->get();
        $earnedSlugs = $user->badges()->pluck('slug')->toArray();

        foreach ($activeBadges as $badge) {
            // Salta se già vinto
            if (in_array($badge->slug, $earnedSlugs)) {
                continue;
            }

            if ($this->checkCondition($user, $badge)) {
                $this->award($user, $badge);
                $awarded->push($badge);
            }
        }

        return $awarded;
    }

    /**
     * Assegna il badge "Nuovo Utente" alla registrazione.
     */
    public function awardRegistrationBadge(User $user): void
    {
        $badge = Badge::where('slug', 'nuovo-utente')->active()->first();

        if ($badge && !$badge->isEarnedBy($user)) {
            $this->award($user, $badge);
        }
    }

    /**
     * Assegna un badge all'utente e invia la notifica.
     */
    protected function award(User $user, Badge $badge): void
    {
        $user->badges()->attach($badge->id, [
            'earned_at' => now(),
        ]);

        try {
            $user->notify(new BadgeEarnedNotification($badge));
        } catch (\Throwable $e) {
            Log::warning("Notifica badge '{$badge->slug}' non inviata per utente #{$user->id}: {$e->getMessage()}");
        }
    }

    /**
     * Verifica la condizione di un badge per l'utente.
     */
    protected function checkCondition(User $user, Badge $badge): bool
    {
        return match ($badge->slug) {
            'nuovo-utente' => true, // Assegnato alla registrazione, non qui
            'rilevatore-in-erba' => $this->checkFirstTrack($user),
            'rilevatore-1-stella' => $this->checkDailyStreakWeek($user),
            'biker-1-stella' => $this->checkBikeDaysWeek($user),
            'mobilita-collettiva-1-stella' => $this->checkTplCount($user, 1),
            'mobilita-collettiva-2-stelle' => $this->checkTplCount($user, 5),
            'bike-surfer-1-stella' => $this->checkBikeDistance($user, 10),
            'tpl-surfer-1-stella' => $this->checkTplDistance($user, 25),
            'multi-surfer-1-stella' => $this->checkMultiModalDistance($user, 100),
            'multi-surfer-2-stelle' => $this->checkMultiModalDistance($user, 250),
            'ecologista-1-stella' => $this->checkCo2Saved($user, 25),
            'salutista-1-stella' => $this->checkCalories($user, 750),
            'salutista-2-stelle' => $this->checkCalories($user, 2250),
            'salutista-3-stelle' => $this->checkCalories($user, 4500),
            'risparmiatore-1-stella' => $this->checkMoneySaved($user, 6),
            'risparmiatore-2-stelle' => $this->checkMoneySaved($user, 15),
            'risparmiatore-3-stelle' => $this->checkMoneySaved($user, 30),
            default => false,
        };
    }

    // ==================== CHECK SPECIFICI ====================

    /**
     * Prima traccia validata
     */
    protected function checkFirstTrack(User $user): bool
    {
        return $user->validTracks()->exists();
    }

    /**
     * Attività registrata ogni giorno per una settimana solare (lun-dom)
     */
    protected function checkDailyStreakWeek(User $user): bool
    {
        // Controlla le ultime 8 settimane solari (per performance)
        $startDate = now()->subWeeks(8)->startOfWeek(Carbon::MONDAY);

        $trackDates = $user->validTracks()
            ->where('started_at', '>=', $startDate)
            ->pluck('started_at')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
            ->unique()
            ->toArray();

        // Verifica se esiste almeno una settimana solare completa (lun-dom)
        $currentWeekStart = $startDate->copy();
        $today = now()->endOfDay();

        while ($currentWeekStart->lt($today)) {
            $weekEnd = $currentWeekStart->copy()->endOfWeek(Carbon::SUNDAY);

            // Salta settimane non ancora completate
            if ($weekEnd->gt($today)) {
                $currentWeekStart->addWeek();
                continue;
            }

            $allDaysCovered = true;
            for ($day = 0; $day < 7; $day++) {
                $date = $currentWeekStart->copy()->addDays($day)->format('Y-m-d');
                if (!in_array($date, $trackDates)) {
                    $allDaysCovered = false;
                    break;
                }
            }

            if ($allDaysCovered) {
                return true;
            }

            $currentWeekStart->addWeek();
        }

        return false;
    }

    /**
     * Bici usata in almeno 3 giorni diversi in una settimana solare
     */
    protected function checkBikeDaysWeek(User $user): bool
    {
        $startDate = now()->subWeeks(8)->startOfWeek(Carbon::MONDAY);

        // Giorni con tracce in bici raggruppati per settimana
        $bikeDays = $user->validTracks()
            ->where('started_at', '>=', $startDate)
            ->whereHas('segments', fn($q) => $q->where('transport_mode', TransportMode::BIKE))
            ->pluck('started_at')
            ->map(function ($date) {
                $carbon = Carbon::parse($date);
                return [
                    'week' => $carbon->startOfWeek(Carbon::MONDAY)->format('Y-m-d'),
                    'day' => $carbon->format('Y-m-d'),
                ];
            });

        // Raggruppa per settimana e conta giorni unici
        $weekDays = $bikeDays->groupBy('week')->map(function ($days) {
            return $days->pluck('day')->unique()->count();
        });

        return $weekDays->contains(fn($count) => $count >= 3);
    }

    /**
     * Numero di tracce con trasporto pubblico (treno o bus)
     */
    protected function checkTplCount(User $user, int $threshold): bool
    {
        $count = $user->validTracks()
            ->whereHas('segments', function ($q) {
                $q->whereIn('transport_mode', [TransportMode::TRAIN, TransportMode::BUS]);
            })
            ->count();

        return $count >= $threshold;
    }

    /**
     * Distanza totale in bici (km)
     */
    protected function checkBikeDistance(User $user, float $thresholdKm): bool
    {
        $totalMeters = TrackSegment::whereHas('track', function ($q) use ($user) {
                $q->where('user_id', $user->id)->valid();
            })
            ->where('transport_mode', TransportMode::BIKE)
            ->where('status', 'valid')
            ->sum('distance_meters');

        return ($totalMeters / 1000) >= $thresholdKm;
    }

    /**
     * Distanza totale in TPL (km)
     */
    protected function checkTplDistance(User $user, float $thresholdKm): bool
    {
        $totalMeters = TrackSegment::whereHas('track', function ($q) use ($user) {
                $q->where('user_id', $user->id)->valid();
            })
            ->whereIn('transport_mode', [TransportMode::TRAIN, TransportMode::BUS])
            ->where('status', 'valid')
            ->sum('distance_meters');

        return ($totalMeters / 1000) >= $thresholdKm;
    }

    /**
     * Distanza multi-modale: almeno 2 modalità sostenibili con tot km
     */
    protected function checkMultiModalDistance(User $user, float $thresholdKm): bool
    {
        $sustainableModes = [
            TransportMode::BIKE,
            TransportMode::WALK,
            TransportMode::TRAIN,
            TransportMode::BUS,
        ];

        $distanceByMode = TrackSegment::whereHas('track', function ($q) use ($user) {
                $q->where('user_id', $user->id)->valid();
            })
            ->whereIn('transport_mode', $sustainableModes)
            ->where('status', 'valid')
            ->selectRaw('transport_mode, SUM(distance_meters) as total_meters')
            ->groupBy('transport_mode')
            ->pluck('total_meters', 'transport_mode');

        // Almeno 2 modalità utilizzate
        $usedModes = $distanceByMode->filter(fn($meters) => $meters > 0)->count();
        if ($usedModes < 2) {
            return false;
        }

        // Distanza totale
        $totalKm = $distanceByMode->sum() / 1000;

        return $totalKm >= $thresholdKm;
    }

    /**
     * CO2 risparmiata totale (kg)
     */
    protected function checkCo2Saved(User $user, float $thresholdKg): bool
    {
        $totalGrams = $user->validTracks()->sum('co2_saved_grams');

        return ($totalGrams / 1000) >= $thresholdKg;
    }

    /**
     * Calorie totali bruciate
     */
    protected function checkCalories(User $user, float $threshold): bool
    {
        $totalCalories = $user->validTracks()->sum('calories_burned');

        return $totalCalories >= $threshold;
    }

    /**
     * Risparmio economico totale (euro)
     * Calcolato sommando il risparmio di tutti i segmenti validi
     */
    protected function checkMoneySaved(User $user, float $thresholdEuro): bool
    {
        $totalSaved = TrackSegment::whereHas('track', function ($q) use ($user) {
                $q->where('user_id', $user->id)->valid();
            })
            ->where('status', 'valid')
            ->get()
            ->sum(function (TrackSegment $segment) {
                return (new EmissionsCalculator())->calculateMoneySaved($segment);
            });

        return $totalSaved >= $thresholdEuro;
    }
}
