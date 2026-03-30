<?php

namespace Tests\Unit;

use App\Rules\CpfRule;
use PHPUnit\Framework\TestCase;

class CpfRuleTest extends TestCase
{
    private function applyRule(string $value): ?string
    {
        $failMessage = null;

        (new CpfRule())->validate('cpf', $value, function (string $message) use (&$failMessage): void {
            $failMessage = $message;
        });

        return $failMessage;
    }

    public function test_passes_for_valid_cpf(): void
    {
        $message = $this->applyRule('52998224725');

        $this->assertNull($message);
    }

    public function test_fails_for_invalid_cpf(): void
    {
        $message = $this->applyRule('00000000000');

        $this->assertNotNull($message);
        $this->assertStringContainsString('inválido', $message);
    }

    public function test_fails_for_empty_value(): void
    {
        $message = $this->applyRule('');

        $this->assertNotNull($message);
    }

    public function test_fail_message_contains_attribute_name(): void
    {
        $failMessage = null;

        (new CpfRule())->validate('cpf', '00000000000', function (string $message) use (&$failMessage): void {
            $failMessage = $message;
        });

        $this->assertStringContainsString('cpf', $failMessage);
    }

    public function test_passes_for_masked_valid_cpf(): void
    {
        $message = $this->applyRule('529.982.247-25');

        $this->assertNull($message);
    }
}
