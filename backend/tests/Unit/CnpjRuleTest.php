<?php

namespace Tests\Unit;

use App\Rules\CnpjRule;
use PHPUnit\Framework\TestCase;

class CnpjRuleTest extends TestCase
{
    private function applyRule(string $value): ?string
    {
        $failMessage = null;

        (new CnpjRule())->validate('cnpj', $value, function (string $message) use (&$failMessage): void {
            $failMessage = $message;
        });

        return $failMessage;
    }

    public function test_passes_for_valid_cnpj(): void
    {
        $message = $this->applyRule('11222333000181');

        $this->assertNull($message);
    }

    public function test_passes_for_masked_valid_cnpj(): void
    {
        $message = $this->applyRule('11.222.333/0001-81');

        $this->assertNull($message);
    }

    public function test_fails_for_invalid_cnpj(): void
    {
        $message = $this->applyRule('00000000000000');

        $this->assertNotNull($message);
    }

    public function test_fails_for_empty_value(): void
    {
        $message = $this->applyRule('');

        $this->assertNotNull($message);
    }

    public function test_fail_message_is_correct(): void
    {
        $message = $this->applyRule('00000000000000');

        $this->assertSame('O CNPJ informado é inválido.', $message);
    }

    public function test_fails_for_cnpj_with_wrong_check_digit(): void
    {
        // CNPJ válido com último dígito alterado
        $message = $this->applyRule('11222333000182');

        $this->assertNotNull($message);
        $this->assertSame('O CNPJ informado é inválido.', $message);
    }
}
