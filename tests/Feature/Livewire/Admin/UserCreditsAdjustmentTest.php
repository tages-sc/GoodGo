<?php

namespace Tests\Feature\Livewire\Admin;

use App\Enums\CreditLogType;
use App\Enums\UserType;
use App\Livewire\Admin\Users\Edit;
use App\Models\CreditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserCreditsAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['type' => UserType::SUPER_ADMIN]);
        $this->user = User::factory()->create(['type' => UserType::USER, 'credits' => 0]);
        $this->actingAs($this->admin);
    }

    public function test_admin_can_add_credits_and_log_is_created(): void
    {
        Livewire::test(Edit::class, ['user' => $this->user])
            ->set('creditsOperation', 'add')
            ->set('creditsAmount', '10')
            ->set('creditsDescription', 'regalo di benvenuto')
            ->call('adjustCredits')
            ->assertHasNoErrors();

        $this->assertEquals(10.0, (float) $this->user->fresh()->credits);

        $log = CreditLog::where('user_id', $this->user->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals(CreditLogType::MANUAL_ADD, $log->type);
        $this->assertEquals(10.0, (float) $log->amount);
        $this->assertEquals(0.0, (float) $log->balance_before);
        $this->assertEquals(10.0, (float) $log->balance_after);
        $this->assertEquals('regalo di benvenuto', $log->description);
        $this->assertEquals(User::class, $log->causer_type);
        $this->assertEquals($this->admin->id, $log->causer_id);
    }

    public function test_admin_can_subtract_credits(): void
    {
        $this->user->update(['credits' => 20]);

        Livewire::test(Edit::class, ['user' => $this->user])
            ->set('creditsOperation', 'subtract')
            ->set('creditsAmount', '5')
            ->set('creditsDescription', 'rettifica manuale')
            ->call('adjustCredits')
            ->assertHasNoErrors();

        $this->assertEquals(15.0, (float) $this->user->fresh()->credits);

        $log = CreditLog::where('user_id', $this->user->id)->first();
        $this->assertEquals(CreditLogType::MANUAL_SUBTRACT, $log->type);
        $this->assertEquals(-5.0, (float) $log->amount);
        $this->assertEquals(20.0, (float) $log->balance_before);
        $this->assertEquals(15.0, (float) $log->balance_after);
    }

    public function test_admin_cannot_subtract_more_than_balance(): void
    {
        $this->user->update(['credits' => 3]);

        Livewire::test(Edit::class, ['user' => $this->user])
            ->set('creditsOperation', 'subtract')
            ->set('creditsAmount', '10')
            ->set('creditsDescription', 'test')
            ->call('adjustCredits')
            ->assertHasErrors(['creditsAmount']);

        $this->assertEquals(3.0, (float) $this->user->fresh()->credits);
        $this->assertDatabaseCount('credits_log', 0);
    }

    public function test_amount_must_be_positive(): void
    {
        Livewire::test(Edit::class, ['user' => $this->user])
            ->set('creditsOperation', 'add')
            ->set('creditsAmount', '0')
            ->set('creditsDescription', 'test')
            ->call('adjustCredits')
            ->assertHasErrors(['creditsAmount']);
    }

    public function test_description_is_required(): void
    {
        Livewire::test(Edit::class, ['user' => $this->user])
            ->set('creditsOperation', 'add')
            ->set('creditsAmount', '5')
            ->set('creditsDescription', '')
            ->call('adjustCredits')
            ->assertHasErrors(['creditsDescription']);
    }

    public function test_admin_credits_count_as_earned_in_user_dashboard(): void
    {
        // Regression della domanda: "ho 10 crediti ma 0 guadagnati".
        // Dopo il refactoring l'accredito admin risulta nella dashboard utente.
        Livewire::test(Edit::class, ['user' => $this->user])
            ->set('creditsOperation', 'add')
            ->set('creditsAmount', '10')
            ->set('creditsDescription', 'regalo')
            ->call('adjustCredits');

        $totalEarned = (float) $this->user->fresh()->creditLogs()
            ->whereIn('type', ['track_validation', 'manual_add', 'movement_refund', 'movement_reward', 'bonus'])
            ->sum('amount');

        $this->assertEquals(10.0, $totalEarned);
    }

    public function test_form_is_reset_after_successful_adjustment(): void
    {
        Livewire::test(Edit::class, ['user' => $this->user])
            ->set('creditsOperation', 'add')
            ->set('creditsAmount', '5')
            ->set('creditsDescription', 'test')
            ->call('adjustCredits')
            ->assertSet('creditsAmount', '')
            ->assertSet('creditsDescription', '')
            ->assertSet('creditsOperation', 'add');
    }
}
