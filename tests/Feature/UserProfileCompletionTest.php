<?php

namespace Tests\Feature;

use App\Enums\UserType;
use App\Models\EnteProfile;
use App\Models\PartnerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileCompletionTest extends TestCase
{
    use RefreshDatabase;

    // ==================== PARTNER ====================

    public function test_partner_without_profile_is_incomplete(): void
    {
        $partner = User::factory()->create(['type' => UserType::PARTNER]);

        $this->assertFalse($partner->isProfileComplete());
        $this->assertContains('Profilo partner non creato', $partner->missingProfileFields());
    }

    public function test_partner_with_all_required_fields_is_complete(): void
    {
        $partner = User::factory()->create(['type' => UserType::PARTNER]);
        PartnerProfile::create($this->completePartnerFields($partner->id));

        $this->assertTrue($partner->fresh()->isProfileComplete());
        $this->assertEmpty($partner->fresh()->missingProfileFields());
    }

    public function test_partner_missing_iban_lists_iban_field(): void
    {
        $partner = User::factory()->create(['type' => UserType::PARTNER]);
        PartnerProfile::create(array_merge(
            $this->completePartnerFields($partner->id),
            ['iban' => null]
        ));

        $missing = $partner->fresh()->missingProfileFields();
        $this->assertSame(['IBAN'], $missing);
    }

    public function test_partner_missing_multiple_fields_lists_all_labels(): void
    {
        $partner = User::factory()->create(['type' => UserType::PARTNER]);
        PartnerProfile::create(array_merge(
            $this->completePartnerFields($partner->id),
            ['iban' => null, 'pec' => null, 'legal_rep_birth_date' => null]
        ));

        $missing = $partner->fresh()->missingProfileFields();
        $this->assertContains('IBAN', $missing);
        $this->assertContains('PEC azienda', $missing);
        $this->assertContains('Data di nascita legale rappresentante', $missing);
        $this->assertCount(3, $missing);
    }

    public function test_partner_social_urls_and_second_operator_are_not_required(): void
    {
        // Conferma: campi esclusi dal requisito non fanno considerare il profilo incompleto
        $partner = User::factory()->create(['type' => UserType::PARTNER]);
        PartnerProfile::create($this->completePartnerFields($partner->id));

        $this->assertTrue($partner->fresh()->isProfileComplete());
    }

    // ==================== ENTE / ORGANIZER ====================

    public function test_ente_without_profile_is_incomplete(): void
    {
        $ente = User::factory()->create(['type' => UserType::ENTE]);

        $this->assertFalse($ente->isProfileComplete());
        $this->assertContains('Profilo ente non creato', $ente->missingProfileFields());
    }

    public function test_ente_with_all_required_fields_is_complete(): void
    {
        $ente = User::factory()->create(['type' => UserType::ENTE]);
        EnteProfile::create($this->completeEnteFields($ente->id));

        $this->assertTrue($ente->fresh()->isProfileComplete());
        $this->assertEmpty($ente->fresh()->missingProfileFields());
    }

    public function test_ente_missing_descrizione_lists_descrizione_field(): void
    {
        $ente = User::factory()->create(['type' => UserType::ENTE]);
        EnteProfile::create(array_merge(
            $this->completeEnteFields($ente->id),
            ['descrizione' => null]
        ));

        $missing = $ente->fresh()->missingProfileFields();
        $this->assertSame(['Descrizione'], $missing);
    }

    public function test_ente_social_urls_are_not_required(): void
    {
        // Conferma esplicita: social URLs non sono tra i requisiti
        $ente = User::factory()->create(['type' => UserType::ENTE]);
        EnteProfile::create(array_merge(
            $this->completeEnteFields($ente->id),
            [
                'instagram_url' => null,
                'linkedin_url' => null,
                'twitter_url' => null,
                'facebook_url' => null,
            ]
        ));

        $this->assertTrue($ente->fresh()->isProfileComplete());
    }

    public function test_organizer_uses_same_rules_as_ente(): void
    {
        $organizer = User::factory()->create(['type' => UserType::ORGANIZER]);
        EnteProfile::create(array_merge(
            $this->completeEnteFields($organizer->id),
            ['location' => null]
        ));

        $missing = $organizer->fresh()->missingProfileFields();
        $this->assertSame(['Location'], $missing);
    }

    // ==================== NO CHECK TYPES ====================

    public function test_cittadino_never_requires_profile_fields(): void
    {
        $user = User::factory()->create(['type' => UserType::USER]);

        $this->assertTrue($user->isProfileComplete());
        $this->assertEmpty($user->missingProfileFields());
    }

    public function test_super_admin_never_requires_profile_fields(): void
    {
        $admin = User::factory()->create(['type' => UserType::SUPER_ADMIN]);

        $this->assertTrue($admin->isProfileComplete());
        $this->assertEmpty($admin->missingProfileFields());
    }

    // ==================== HELPERS ====================

    private function completePartnerFields(int $userId): array
    {
        return [
            'user_id' => $userId,
            'company_name' => 'Acme SRL',
            'vat_number' => '12345678901',
            'fiscal_code' => 'CMPNME80A01H501Z',
            'company_address' => 'Via Roma 1',
            'company_city' => 'Pisa',
            'company_postal_code' => '56100',
            'company_email' => 'info@acme.it',
            'pec' => 'acme@pec.it',
            'legal_address' => 'Via Milano 2',
            'legal_city' => 'Pisa',
            'legal_postal_code' => '56100',
            'legal_rep_name' => 'Mario',
            'legal_rep_surname' => 'Rossi',
            'legal_rep_fiscal_code' => 'RSSMRA80A01H501Z',
            'legal_rep_birth_place' => 'Pisa',
            'legal_rep_birth_date' => '1980-01-01',
            'legal_rep_address' => 'Via Torino 3',
            'legal_rep_city' => 'Pisa',
            'legal_rep_postal_code' => '56100',
            'iban' => 'IT60X0542811101000000123456',
            'bank_name' => 'Banca Intesa',
            'operator_name' => 'Luigi',
            'operator_surname' => 'Bianchi',
            'operator_fiscal_code' => 'BNCLGU80A01H501Z',
        ];
    }

    private function completeEnteFields(int $userId): array
    {
        return [
            'user_id' => $userId,
            'tipologia' => 'comune',
            'location' => 'Pisa',
            'descrizione' => 'Ente test',
            'website' => 'https://example.com',
            'colore' => '#0055aa',
        ];
    }
}
