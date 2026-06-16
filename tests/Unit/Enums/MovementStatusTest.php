<?php

namespace Tests\Unit\Enums;

use App\Enums\MovementStatus;
use PHPUnit\Framework\TestCase;

class MovementStatusTest extends TestCase
{
    public function test_only_pending_is_processable(): void
    {
        $this->assertTrue(MovementStatus::PENDING->canBeProcessed());
        $this->assertFalse(MovementStatus::APPROVED->canBeProcessed());
        $this->assertFalse(MovementStatus::REJECTED->canBeProcessed());
        $this->assertFalse(MovementStatus::CANCELLED->canBeProcessed());
    }

    public function test_final_states(): void
    {
        $this->assertFalse(MovementStatus::PENDING->isFinal());
        $this->assertTrue(MovementStatus::APPROVED->isFinal());
        $this->assertTrue(MovementStatus::REJECTED->isFinal());
        $this->assertTrue(MovementStatus::CANCELLED->isFinal());
    }

    public function test_labels(): void
    {
        $this->assertEquals('In attesa', MovementStatus::PENDING->label());
        $this->assertEquals('Approvato', MovementStatus::APPROVED->label());
        $this->assertEquals('Rifiutato', MovementStatus::REJECTED->label());
        $this->assertEquals('Annullato', MovementStatus::CANCELLED->label());
    }
}
