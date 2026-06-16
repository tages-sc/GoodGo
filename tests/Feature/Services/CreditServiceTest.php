<?php

namespace Tests\Feature\Services;

use App\Enums\CreditLogType;
use App\Enums\MovementStatus;
use App\Enums\MovementType;
use App\Enums\UserType;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditServiceTest extends TestCase
{
    use RefreshDatabase;

    private CreditService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CreditService();
    }

    private function createUser(float $credits = 0, UserType $type = UserType::USER): User
    {
        return User::factory()->create([
            'type' => $type,
            'credits' => $credits,
        ]);
    }

    // === ADD CREDITS ===

    public function test_add_credits_increases_balance(): void
    {
        $user = $this->createUser(100);
        $admin = $this->createUser(0, UserType::SUPER_ADMIN);

        $log = $this->service->addCredits($user, 50, CreditLogType::MANUAL_ADD, 'Test', $admin);

        $user->refresh();
        $this->assertEquals(150, $user->credits);
        $this->assertEquals(50, $log->amount);
        $this->assertEquals(100, $log->balance_before);
        $this->assertEquals(150, $log->balance_after);
    }

    public function test_add_credits_rejects_zero_amount(): void
    {
        $user = $this->createUser(100);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->addCredits($user, 0, CreditLogType::MANUAL_ADD);
    }

    public function test_add_credits_rejects_negative_amount(): void
    {
        $user = $this->createUser(100);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->addCredits($user, -10, CreditLogType::MANUAL_ADD);
    }

    // === SUBTRACT CREDITS ===

    public function test_subtract_credits_decreases_balance(): void
    {
        $user = $this->createUser(100);
        $admin = $this->createUser(0, UserType::SUPER_ADMIN);

        $log = $this->service->subtractCredits($user, 30, CreditLogType::MANUAL_SUBTRACT, 'Test', $admin);

        $user->refresh();
        $this->assertEquals(70, $user->credits);
        $this->assertEquals(-30, $log->amount);
    }

    public function test_subtract_credits_fails_with_insufficient_balance(): void
    {
        $user = $this->createUser(10);
        $admin = $this->createUser(0, UserType::SUPER_ADMIN);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->subtractCredits($user, 50, CreditLogType::MANUAL_SUBTRACT, null, $admin);
    }

    public function test_subtract_exact_balance_succeeds(): void
    {
        $user = $this->createUser(50);
        $admin = $this->createUser(0, UserType::SUPER_ADMIN);

        $this->service->subtractCredits($user, 50, CreditLogType::MANUAL_SUBTRACT, null, $admin);

        $user->refresh();
        $this->assertEquals(0, $user->credits);
    }

    // === ADJUST CREDITS ===

    public function test_adjust_credits_positive(): void
    {
        $user = $this->createUser(100);
        $admin = $this->createUser(0, UserType::SUPER_ADMIN);

        $log = $this->service->adjustCredits($user, 25, 'Bonus', $admin);

        $user->refresh();
        $this->assertEquals(125, $user->credits);
        $this->assertEquals(CreditLogType::MANUAL_ADD, $log->type);
    }

    public function test_adjust_credits_negative(): void
    {
        $user = $this->createUser(100);
        $admin = $this->createUser(0, UserType::SUPER_ADMIN);

        $log = $this->service->adjustCredits($user, -25, 'Penalita', $admin);

        $user->refresh();
        $this->assertEquals(75, $user->credits);
        $this->assertEquals(CreditLogType::MANUAL_SUBTRACT, $log->type);
    }

    // === EXPENSE REQUEST ===

    public function test_create_expense_request(): void
    {
        $user = $this->createUser(100);
        $partner = $this->createUser(0, UserType::PARTNER);

        $movement = $this->service->createExpenseRequest($user, $partner, 30, 'Pizza', null, 3.0);

        $this->assertEquals(MovementStatus::PENDING, $movement->status);
        $this->assertEquals(MovementType::EXPENSE, $movement->type);
        $this->assertEquals(30, $movement->credits_amount);
        $this->assertEquals(3.0, $movement->euro_amount);
        $this->assertEquals($partner->id, $movement->partner_id);
    }

    public function test_create_expense_request_fails_insufficient_credits(): void
    {
        $user = $this->createUser(10);
        $partner = $this->createUser(0, UserType::PARTNER);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->createExpenseRequest($user, $partner, 50, 'Too much');
    }

    public function test_create_expense_request_fails_non_partner(): void
    {
        $user = $this->createUser(100);
        $notPartner = $this->createUser(0, UserType::USER);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->createExpenseRequest($user, $notPartner, 10, 'Test');
    }

    // === APPROVE MOVEMENT ===

    public function test_approve_expense_subtracts_credits(): void
    {
        $user = $this->createUser(100);
        $partner = $this->createUser(0, UserType::PARTNER);
        $admin = $this->createUser(0, UserType::SUPER_ADMIN);

        $movement = $this->service->createExpenseRequest($user, $partner, 30, 'Spesa');
        $approved = $this->service->approveMovement($movement, $admin);

        $user->refresh();
        $this->assertEquals(70, $user->credits);
        $this->assertEquals(MovementStatus::APPROVED, $approved->status);
        $this->assertNotNull($approved->processed_at);
    }

    // === REJECT MOVEMENT ===

    public function test_reject_movement(): void
    {
        $user = $this->createUser(100);
        $partner = $this->createUser(0, UserType::PARTNER);
        $admin = $this->createUser(0, UserType::SUPER_ADMIN);

        $movement = $this->service->createExpenseRequest($user, $partner, 30, 'Spesa');
        $rejected = $this->service->rejectMovement($movement, $admin, 'Non valido');

        $user->refresh();
        $this->assertEquals(100, $user->credits); // Credits unchanged
        $this->assertEquals(MovementStatus::REJECTED, $rejected->status);
        $this->assertEquals('Non valido', $rejected->rejection_reason);
    }

    // === CANCEL MOVEMENT ===

    public function test_cancel_pending_movement(): void
    {
        $user = $this->createUser(100);
        $partner = $this->createUser(0, UserType::PARTNER);

        $movement = $this->service->createExpenseRequest($user, $partner, 30, 'Spesa');
        $cancelled = $this->service->cancelMovement($movement);

        $this->assertEquals(MovementStatus::CANCELLED, $cancelled->status);
    }

    public function test_cancel_approved_movement_fails(): void
    {
        $user = $this->createUser(100);
        $partner = $this->createUser(0, UserType::PARTNER);
        $admin = $this->createUser(0, UserType::SUPER_ADMIN);

        $movement = $this->service->createExpenseRequest($user, $partner, 30, 'Spesa');
        $this->service->approveMovement($movement, $admin);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->cancelMovement($movement->fresh());
    }

    // === STATS ===

    public function test_get_balance(): void
    {
        $user = $this->createUser(42.50);
        $this->assertEquals(42.50, $this->service->getBalance($user));
    }

    public function test_get_stats(): void
    {
        $user = $this->createUser(0);
        $admin = $this->createUser(0, UserType::SUPER_ADMIN);

        $this->service->addCredits($user, 100, CreditLogType::TRACK_VALIDATION, null, $admin);
        $this->service->addCredits($user, 50, CreditLogType::BONUS, null, $admin);

        $user->refresh();
        $stats = $this->service->getStats($user);

        $this->assertEquals(150, $stats['current_balance']);
        $this->assertEquals(150, $stats['total_earned']);
    }
}
