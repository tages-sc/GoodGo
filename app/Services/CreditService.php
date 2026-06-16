<?php

namespace App\Services;

use App\Enums\CreditLogType;
use App\Enums\MovementStatus;
use App\Enums\MovementType;
use App\Models\CreditLog;
use App\Models\Movement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreditService
{
    /**
     * Aggiunge crediti a un utente
     */
    public function addCredits(
        User $user,
        float $amount,
        CreditLogType $type,
        ?string $description = null,
        ?object $causer = null,
        ?array $metadata = null
    ): CreditLog {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('L\'importo deve essere positivo per aggiungere crediti.');
        }

        return $this->modifyCredits($user, $amount, $type, $description, $causer, $metadata);
    }

    /**
     * Sottrae crediti da un utente
     */
    public function subtractCredits(
        User $user,
        float $amount,
        CreditLogType $type,
        ?string $description = null,
        ?object $causer = null,
        ?array $metadata = null
    ): CreditLog {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('L\'importo deve essere positivo per sottrarre crediti.');
        }

        // Verifica che l'utente abbia crediti sufficienti
        if ($user->credits < $amount) {
            throw new \InvalidArgumentException('Crediti insufficienti. Disponibili: ' . $user->credits);
        }

        return $this->modifyCredits($user, -$amount, $type, $description, $causer, $metadata);
    }

    /**
     * Modifica i crediti di un utente (interno)
     */
    protected function modifyCredits(
        User $user,
        float $amount,
        CreditLogType $type,
        ?string $description,
        ?object $causer,
        ?array $metadata
    ): CreditLog {
        return DB::transaction(function () use ($user, $amount, $type, $description, $causer, $metadata) {
            // Blocca la riga utente per evitare race conditions
            $user = User::lockForUpdate()->find($user->id);

            $balanceBefore = $user->credits;
            $balanceAfter = $balanceBefore + $amount;

            // Aggiorna il saldo utente
            $user->update(['credits' => $balanceAfter]);

            // Crea il log
            $log = CreditLog::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'type' => $type,
                'description' => $description,
                'causer_type' => $causer ? get_class($causer) : null,
                'causer_id' => $causer?->id,
                'metadata' => $metadata,
            ]);

            return $log;
        });
    }

    /**
     * Rettifica manuale dei crediti
     */
    public function adjustCredits(
        User $user,
        float $amount,
        string $reason,
        User $adjustedBy
    ): CreditLog {
        $type = $amount >= 0 ? CreditLogType::MANUAL_ADD : CreditLogType::MANUAL_SUBTRACT;

        return $this->modifyCredits(
            $user,
            $amount,
            $type,
            $reason,
            $adjustedBy,
            ['adjusted_by' => $adjustedBy->id, 'adjusted_by_name' => $adjustedBy->name]
        );
    }

    /**
     * Crea una richiesta di movimento (spesa)
     */
    public function createExpenseRequest(
        User $user,
        User $partner,
        float $creditsAmount,
        string $description,
        ?int $competitionId = null,
        ?float $euroAmount = null
    ): Movement {
        // Verifica che l'utente abbia crediti sufficienti
        if ($user->credits < $creditsAmount) {
            throw new \InvalidArgumentException('Crediti insufficienti. Disponibili: ' . $user->credits);
        }

        // Verifica che il partner sia effettivamente un partner
        if (!$partner->isPartner()) {
            throw new \InvalidArgumentException('L\'utente selezionato non è un partner commerciale.');
        }

        return Movement::create([
            'user_id' => $user->id,
            'partner_id' => $partner->id,
            'competition_id' => $competitionId,
            'credits_amount' => $creditsAmount,
            'euro_amount' => $euroAmount,
            'exchange_rate' => $euroAmount && $creditsAmount > 0 ? $euroAmount / $creditsAmount : 1,
            'type' => MovementType::EXPENSE,
            'status' => MovementStatus::PENDING,
            'description' => $description,
        ]);
    }

    /**
     * Approva un movimento
     */
    public function approveMovement(Movement $movement, User $approvedBy): Movement
    {
        if (!$movement->canBeProcessed()) {
            throw new \InvalidArgumentException('Il movimento non può essere processato.');
        }

        return DB::transaction(function () use ($movement, $approvedBy) {
            $user = $movement->user;

            // Per le spese, sottrai i crediti
            if ($movement->type === MovementType::EXPENSE) {
                if ($user->credits < $movement->credits_amount) {
                    throw new \InvalidArgumentException('Crediti insufficienti per approvare il movimento.');
                }

                $creditLog = $this->subtractCredits(
                    $user,
                    $movement->credits_amount,
                    CreditLogType::MOVEMENT_EXPENSE,
                    "Spesa presso {$movement->partner->name}: {$movement->description}",
                    $movement,
                    [
                        'movement_id' => $movement->id,
                        'partner_id' => $movement->partner_id,
                        'partner_name' => $movement->partner->name,
                    ]
                );

                $movement->credit_log_id = $creditLog->id;
            }

            // Per premi e rimborsi, aggiungi i crediti
            if (in_array($movement->type, [MovementType::REWARD, MovementType::REFUND])) {
                $logType = $movement->type === MovementType::REWARD
                    ? CreditLogType::MOVEMENT_REWARD
                    : CreditLogType::MOVEMENT_REFUND;

                $creditLog = $this->addCredits(
                    $user,
                    $movement->credits_amount,
                    $logType,
                    $movement->description,
                    $movement,
                    ['movement_id' => $movement->id]
                );

                $movement->credit_log_id = $creditLog->id;
            }

            $movement->update([
                'status' => MovementStatus::APPROVED,
                'processed_by' => $approvedBy->id,
                'processed_at' => now(),
            ]);

            return $movement->fresh();
        });
    }

    /**
     * Rifiuta un movimento
     */
    public function rejectMovement(Movement $movement, User $rejectedBy, string $reason): Movement
    {
        if (!$movement->canBeProcessed()) {
            throw new \InvalidArgumentException('Il movimento non può essere processato.');
        }

        $movement->update([
            'status' => MovementStatus::REJECTED,
            'processed_by' => $rejectedBy->id,
            'processed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $movement->fresh();
    }

    /**
     * Annulla un movimento (solo se pending)
     */
    public function cancelMovement(Movement $movement): Movement
    {
        if (!$movement->isPending()) {
            throw new \InvalidArgumentException('Solo i movimenti in attesa possono essere annullati.');
        }

        $movement->update([
            'status' => MovementStatus::CANCELLED,
        ]);

        return $movement->fresh();
    }

    /**
     * Crea un movimento di premio/ricompensa
     */
    public function createRewardMovement(
        User $user,
        float $creditsAmount,
        string $description,
        ?int $competitionId = null,
        ?int $enteId = null
    ): Movement {
        return Movement::create([
            'user_id' => $user->id,
            'competition_id' => $competitionId,
            'ente_id' => $enteId,
            'credits_amount' => $creditsAmount,
            'type' => MovementType::REWARD,
            'status' => MovementStatus::PENDING,
            'description' => $description,
        ]);
    }

    /**
     * Crea e approva immediatamente un movimento di rettifica
     */
    public function createAdjustment(
        User $user,
        float $amount,
        string $reason,
        User $adjustedBy,
        ?int $competitionId = null
    ): Movement {
        return DB::transaction(function () use ($user, $amount, $reason, $adjustedBy, $competitionId) {
            $movement = Movement::create([
                'user_id' => $user->id,
                'competition_id' => $competitionId,
                'credits_amount' => abs($amount),
                'type' => MovementType::ADJUSTMENT,
                'status' => MovementStatus::PENDING,
                'description' => $reason,
                'notes' => $amount < 0 ? 'Sottrazione' : 'Aggiunta',
            ]);

            // Per le rettifiche, applichiamo immediatamente
            $logType = $amount >= 0 ? CreditLogType::MANUAL_ADD : CreditLogType::MANUAL_SUBTRACT;

            if ($amount < 0 && $user->credits < abs($amount)) {
                throw new \InvalidArgumentException('Crediti insufficienti per la rettifica.');
            }

            $creditLog = $this->modifyCredits(
                $user,
                $amount,
                $logType,
                $reason,
                $movement,
                [
                    'movement_id' => $movement->id,
                    'adjusted_by' => $adjustedBy->id,
                ]
            );

            $movement->update([
                'status' => MovementStatus::APPROVED,
                'processed_by' => $adjustedBy->id,
                'processed_at' => now(),
                'credit_log_id' => $creditLog->id,
            ]);

            return $movement->fresh();
        });
    }

    /**
     * Ottiene il saldo crediti di un utente
     */
    public function getBalance(User $user): float
    {
        return $user->credits;
    }

    /**
     * Ottiene lo storico crediti di un utente
     */
    public function getHistory(User $user, int $limit = 50)
    {
        return CreditLog::forUser($user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Ottiene i movimenti pendenti per un partner
     */
    public function getPendingMovementsForPartner(User $partner)
    {
        return Movement::forPartner($partner->id)
            ->pending()
            ->with(['user', 'competition'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Ottiene statistiche crediti per un utente
     */
    public function getStats(User $user): array
    {
        $logs = CreditLog::forUser($user->id);

        return [
            'current_balance' => $user->credits,
            'total_earned' => (clone $logs)->additions()->sum('amount'),
            'total_spent' => abs((clone $logs)->subtractions()->sum('amount')),
            'movements_count' => Movement::forUser($user->id)->approved()->count(),
            'pending_movements' => Movement::forUser($user->id)->pending()->count(),
        ];
    }
}
