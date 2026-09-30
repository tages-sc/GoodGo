<?php

namespace Tests\Feature\Livewire\Admin;

use App\Enums\UserType;
use App\Livewire\Admin\Users\Index;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UsersBulkDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['type' => UserType::SUPER_ADMIN, 'created_at' => now()->subYear()]);
        $this->actingAs($this->admin);
    }

    public function test_select_page_selects_only_deletable_users_of_current_page(): void
    {
        User::factory()->count(20)->create(['type' => UserType::USER]);

        $component = Livewire::test(Index::class)->call('toggleSelectPage');

        $selected = $component->get('selected');
        $this->assertCount(15, $selected);
        $this->assertNotContains($this->admin->id, $selected);

        $component->call('toggleSelectPage');
        $this->assertCount(0, $component->get('selected'));
    }

    public function test_bulk_delete_removes_selected_users_and_skips_super_admin(): void
    {
        $spam = User::factory()->count(3)->create(['type' => UserType::USER]);
        $keep = User::factory()->create(['type' => UserType::USER]);

        Livewire::test(Index::class)
            ->set('selected', [...$spam->pluck('id')->map(fn ($id) => (string) $id)->all(), (string) $this->admin->id])
            ->call('confirmBulkDelete')
            ->assertSet('showBulkDeleteModal', true)
            ->call('bulkDelete')
            ->assertSet('selected', [])
            ->assertSet('showBulkDeleteModal', false);

        $this->assertDatabaseMissing('users', ['id' => $spam[0]->id]);
        $this->assertDatabaseMissing('users', ['id' => $spam[2]->id]);
        $this->assertDatabaseHas('users', ['id' => $keep->id]);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_changing_filters_clears_selection(): void
    {
        $user = User::factory()->create(['type' => UserType::USER]);

        Livewire::test(Index::class)
            ->set('selected', [(string) $user->id])
            ->set('searchEmail', 'foo')
            ->assertSet('selected', []);
    }
}
