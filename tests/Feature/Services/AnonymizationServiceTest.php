<?php

namespace Tests\Feature\Services;

use App\Enums\UserType;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\AnonymizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AnonymizationServiceTest extends TestCase
{
    use RefreshDatabase;

    private AnonymizationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AnonymizationService();
    }

    public function test_user_is_deleted_after_anonymization(): void
    {
        $user = User::factory()->create(['type' => UserType::USER]);
        UserProfile::create(['user_id' => $user->id]);
        $userId = $user->id;

        $this->service->anonymizeAndDelete($user, 'self_deletion');

        $this->assertNull(User::find($userId));
    }

    public function test_anonymization_log_is_created(): void
    {
        $user = User::factory()->create([
            'type' => UserType::USER,
            'email' => 'test@example.com',
        ]);
        UserProfile::create(['user_id' => $user->id]);

        $this->service->anonymizeAndDelete($user, 'self_deletion');

        $log = DB::table('anonymization_logs')
            ->where('original_user_id', $user->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(hash('sha256', 'test@example.com'), $log->email_hash);
        $this->assertEquals('user', $log->user_type);
        $this->assertEquals('self_deletion', $log->action);
    }

    public function test_anonymization_log_contains_summary(): void
    {
        $user = User::factory()->create([
            'type' => UserType::USER,
            'credits' => 42.50,
        ]);
        UserProfile::create(['user_id' => $user->id]);

        $this->service->anonymizeAndDelete($user, 'self_deletion');

        $log = DB::table('anonymization_logs')
            ->where('original_user_id', $user->id)
            ->first();

        $summary = json_decode($log->summary, true);
        $this->assertArrayHasKey('tracks_count', $summary);
        $this->assertArrayHasKey('credits_at_deletion', $summary);
        $this->assertEquals(42.50, $summary['credits_at_deletion']);
    }

    public function test_admin_deletion_records_performer(): void
    {
        $user = User::factory()->create(['type' => UserType::USER]);
        UserProfile::create(['user_id' => $user->id]);
        $admin = User::factory()->create(['type' => UserType::SUPER_ADMIN]);

        $this->service->anonymizeAndDelete($user, 'admin_deletion', $admin->id);

        $log = DB::table('anonymization_logs')
            ->where('original_user_id', $user->id)
            ->first();

        $this->assertEquals($admin->id, $log->performed_by);
        $this->assertEquals('admin_deletion', $log->action);
    }

    public function test_user_profile_is_deleted_with_cascade(): void
    {
        $user = User::factory()->create(['type' => UserType::USER]);
        $profile = UserProfile::create([
            'user_id' => $user->id,
            'username' => 'testuser',
            'phone' => '1234567890',
        ]);

        $this->service->anonymizeAndDelete($user, 'self_deletion');

        $this->assertNull(UserProfile::find($profile->id));
    }
}
