<?php

namespace Tests\Feature\Livewire\Ente;

use App\Enums\CompetitionStatus;
use App\Enums\CompetitionType;
use App\Enums\CreditLogType;
use App\Enums\ExtensionType;
use App\Enums\RewardMode;
use App\Enums\ScoringType;
use App\Enums\UserType;
use App\Livewire\Ente\Competitions\Participants;
use App\Models\Competition;
use App\Models\CreditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ParticipantCreditsTest extends TestCase
{
    use RefreshDatabase;

    private User $ente;
    private User $participant;
    private Competition $competition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ente = User::factory()->create(['type' => UserType::ENTE]);
        $this->participant = User::factory()->create(['type' => UserType::USER, 'credits' => 0]);

        $this->competition = Competition::create([
            'name' => 'Gara Test',
            'ente_id' => $this->ente->id,
            'start_date' => now()->subDays(5),
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
        ]);

        $this->competition->users()->attach($this->participant->id, [
            'status' => 'approved',
            'registered_at' => now(),
            'approved_at' => now(),
            'total_credits' => 0,
        ]);

        $this->actingAs($this->ente);
    }

    public function test_ente_can_add_credits_to_participant_and_log_has_balance_before_and_after(): void
    {
        // Regression: prima del refactoring il log veniva creato senza balance_before,
        // facendo crashare la query con "balance_before cannot be null".
        Livewire::test(Participants::class, ['competition' => $this->competition])
            ->call('openCreditsModal', $this->participant->id)
            ->set('creditsOperation', 'add')
            ->set('creditsAmount', '25')
            ->set('creditsDescription', 'bonus iscrizione')
            ->call('updateCredits')
            ->assertHasNoErrors();

        $this->assertEquals(25.0, (float) $this->participant->fresh()->credits);

        $log = CreditLog::where('user_id', $this->participant->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals(CreditLogType::MANUAL_ADD, $log->type);
        $this->assertEquals(25.0, (float) $log->amount);
        $this->assertEquals(0.0, (float) $log->balance_before);
        $this->assertEquals(25.0, (float) $log->balance_after);
        $this->assertEquals('bonus iscrizione', $log->description);
        $this->assertEquals(User::class, $log->causer_type);
        $this->assertEquals($this->ente->id, $log->causer_id);
        $this->assertEquals(['competition_id' => $this->competition->id], $log->metadata);
    }

    public function test_pivot_total_credits_is_updated_on_add(): void
    {
        Livewire::test(Participants::class, ['competition' => $this->competition])
            ->call('openCreditsModal', $this->participant->id)
            ->set('creditsOperation', 'add')
            ->set('creditsAmount', '30')
            ->set('creditsDescription', 'aggiunta')
            ->call('updateCredits');

        $pivot = $this->competition->users()
            ->where('user_id', $this->participant->id)
            ->first()
            ->pivot;

        $this->assertEquals(30.0, (float) $pivot->total_credits);
    }

    public function test_ente_can_subtract_credits_and_updates_pivot(): void
    {
        $this->participant->update(['credits' => 50]);
        $this->competition->users()->updateExistingPivot($this->participant->id, [
            'total_credits' => 50,
        ]);

        Livewire::test(Participants::class, ['competition' => $this->competition])
            ->call('openCreditsModal', $this->participant->id)
            ->set('creditsOperation', 'subtract')
            ->set('creditsAmount', '20')
            ->set('creditsDescription', 'rettifica')
            ->call('updateCredits')
            ->assertHasNoErrors();

        $this->assertEquals(30.0, (float) $this->participant->fresh()->credits);

        $log = CreditLog::where('user_id', $this->participant->id)->first();
        $this->assertEquals(CreditLogType::MANUAL_SUBTRACT, $log->type);
        $this->assertEquals(-20.0, (float) $log->amount);
        $this->assertEquals(50.0, (float) $log->balance_before);
        $this->assertEquals(30.0, (float) $log->balance_after);

        $pivot = $this->competition->users()
            ->where('user_id', $this->participant->id)
            ->first()
            ->pivot;
        $this->assertEquals(30.0, (float) $pivot->total_credits);
    }

    public function test_ente_cannot_subtract_more_than_balance(): void
    {
        $this->participant->update(['credits' => 5]);

        Livewire::test(Participants::class, ['competition' => $this->competition])
            ->call('openCreditsModal', $this->participant->id)
            ->set('creditsOperation', 'subtract')
            ->set('creditsAmount', '20')
            ->set('creditsDescription', 'test')
            ->call('updateCredits')
            ->assertHasErrors(['creditsAmount']);

        $this->assertEquals(5.0, (float) $this->participant->fresh()->credits);
        $this->assertDatabaseCount('credits_log', 0);
    }

    public function test_amount_must_be_positive(): void
    {
        Livewire::test(Participants::class, ['competition' => $this->competition])
            ->call('openCreditsModal', $this->participant->id)
            ->set('creditsOperation', 'add')
            ->set('creditsAmount', '0')
            ->set('creditsDescription', 'test')
            ->call('updateCredits')
            ->assertHasErrors(['creditsAmount']);
    }

    public function test_description_is_required(): void
    {
        Livewire::test(Participants::class, ['competition' => $this->competition])
            ->call('openCreditsModal', $this->participant->id)
            ->set('creditsOperation', 'add')
            ->set('creditsAmount', '10')
            ->set('creditsDescription', '')
            ->call('updateCredits')
            ->assertHasErrors(['creditsDescription']);
    }
}
