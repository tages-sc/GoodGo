<?php

namespace Tests\Unit\Enums;

use App\Enums\CreditLogType;
use PHPUnit\Framework\TestCase;

class CreditLogTypeTest extends TestCase
{
    public function test_addition_types(): void
    {
        $this->assertTrue(CreditLogType::TRACK_VALIDATION->isAddition());
        $this->assertTrue(CreditLogType::MANUAL_ADD->isAddition());
        $this->assertTrue(CreditLogType::MOVEMENT_REFUND->isAddition());
        $this->assertTrue(CreditLogType::MOVEMENT_REWARD->isAddition());
        $this->assertTrue(CreditLogType::INITIAL->isAddition());
        $this->assertTrue(CreditLogType::BONUS->isAddition());
    }

    public function test_subtraction_types(): void
    {
        $this->assertTrue(CreditLogType::MANUAL_SUBTRACT->isSubtraction());
        $this->assertTrue(CreditLogType::MOVEMENT_EXPENSE->isSubtraction());
        $this->assertTrue(CreditLogType::EXPIRATION->isSubtraction());
    }

    public function test_addition_and_subtraction_are_mutually_exclusive(): void
    {
        foreach (CreditLogType::cases() as $type) {
            if ($type === CreditLogType::ADJUSTMENT) {
                // Adjustment is neither addition nor subtraction
                $this->assertFalse($type->isAddition());
                $this->assertFalse($type->isSubtraction());
                continue;
            }
            $this->assertNotEquals($type->isAddition(), $type->isSubtraction(),
                "Type {$type->value} should be either addition or subtraction, not both/neither");
        }
    }

    public function test_all_types_have_labels(): void
    {
        foreach (CreditLogType::cases() as $type) {
            $this->assertNotEmpty($type->label());
        }
    }
}
