<?php

namespace Tests\Unit\Enums;

use App\Enums\UserType;
use PHPUnit\Framework\TestCase;

class UserTypeTest extends TestCase
{
    public function test_all_user_types_exist(): void
    {
        $this->assertCount(5, UserType::cases());
        $this->assertNotNull(UserType::SUPER_ADMIN);
        $this->assertNotNull(UserType::ENTE);
        $this->assertNotNull(UserType::ORGANIZER);
        $this->assertNotNull(UserType::PARTNER);
        $this->assertNotNull(UserType::USER);
    }

    public function test_values_are_correct(): void
    {
        $this->assertEquals('super_admin', UserType::SUPER_ADMIN->value);
        $this->assertEquals('ente', UserType::ENTE->value);
        $this->assertEquals('organizer', UserType::ORGANIZER->value);
        $this->assertEquals('partner', UserType::PARTNER->value);
        $this->assertEquals('user', UserType::USER->value);
    }

    public function test_labels_are_italian(): void
    {
        $this->assertEquals('Super Admin', UserType::SUPER_ADMIN->label());
        $this->assertEquals('Ente', UserType::ENTE->label());
        $this->assertEquals('Organizzatore Gara', UserType::ORGANIZER->label());
        $this->assertEquals('Partner Commerciale', UserType::PARTNER->label());
        $this->assertEquals('Utente', UserType::USER->label());
    }

    public function test_values_static_method(): void
    {
        $values = UserType::values();
        $this->assertCount(5, $values);
        $this->assertContains('super_admin', $values);
        $this->assertContains('user', $values);
    }

    public function test_badge_classes_return_strings(): void
    {
        foreach (UserType::cases() as $type) {
            $this->assertNotEmpty($type->badgeClasses());
            $this->assertIsString($type->badgeClasses());
        }
    }
}
