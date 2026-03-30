<?php

namespace Tests\Unit;

use App\Helpers\CnpjHelper;
use PHPUnit\Framework\TestCase;

class CnpjHelperTest extends TestCase
{
    // -------------------------------------------------------------------------
    // sanitize()
    // -------------------------------------------------------------------------

    public function test_sanitize_removes_mask(): void
    {
        $this->assertSame('11222333000181', CnpjHelper::sanitize('11.222.333/0001-81'));
    }

    public function test_sanitize_removes_spaces_and_symbols(): void
    {
        $this->assertSame('11222333000181', CnpjHelper::sanitize('11 222 333 0001 81'));
    }

    public function test_sanitize_returns_digits_only_when_already_clean(): void
    {
        $this->assertSame('11222333000181', CnpjHelper::sanitize('11222333000181'));
    }

    public function test_sanitize_handles_null(): void
    {
        $this->assertSame('', CnpjHelper::sanitize(null));
    }

    public function test_sanitize_handles_empty_string(): void
    {
        $this->assertSame('', CnpjHelper::sanitize(''));
    }

    // -------------------------------------------------------------------------
    // isValid()
    // -------------------------------------------------------------------------

    public function test_is_valid_returns_true_for_valid_cnpj(): void
    {
        $this->assertTrue(CnpjHelper::isValid('11222333000181'));
    }

    public function test_is_valid_accepts_masked_cnpj(): void
    {
        $this->assertTrue(CnpjHelper::isValid('11.222.333/0001-81'));
    }

    public function test_is_valid_returns_false_for_wrong_first_check_digit(): void
    {
        // dígito 13 (penúltimo) alterado de 8 para 9
        $this->assertFalse(CnpjHelper::isValid('11222333000191'));
    }

    public function test_is_valid_returns_false_for_wrong_second_check_digit(): void
    {
        // dígito 14 (último) alterado de 1 para 2
        $this->assertFalse(CnpjHelper::isValid('11222333000182'));
    }

    public function test_is_valid_returns_false_for_all_same_digits(): void
    {
        foreach (range(0, 9) as $digit) {
            $cnpj = str_repeat((string) $digit, 14);
            $this->assertFalse(CnpjHelper::isValid($cnpj), "CNPJ '{$cnpj}' deveria ser inválido.");
        }
    }

    public function test_is_valid_returns_false_for_too_short_cnpj(): void
    {
        $this->assertFalse(CnpjHelper::isValid('1122233300018'));
    }

    public function test_is_valid_returns_false_for_too_long_cnpj(): void
    {
        $this->assertFalse(CnpjHelper::isValid('112223330001810'));
    }

    public function test_is_valid_returns_false_for_empty_string(): void
    {
        $this->assertFalse(CnpjHelper::isValid(''));
    }

    public function test_is_valid_returns_false_for_null(): void
    {
        $this->assertFalse(CnpjHelper::isValid(null));
    }

    public function test_is_valid_with_multiple_valid_cnpjs(): void
    {
        $validCnpjs = [
            '11222333000181',
            '11444777000161',
            '34028316000103',
            '60701190000104',
        ];

        foreach ($validCnpjs as $cnpj) {
            $this->assertTrue(CnpjHelper::isValid($cnpj), "CNPJ '{$cnpj}' deveria ser válido.");
        }
    }

    public function test_is_valid_with_multiple_masked_valid_cnpjs(): void
    {
        $validCnpjs = [
            '11.222.333/0001-81',
            '11.444.777/0001-61',
            '34.028.316/0001-03',
            '60.701.190/0001-04',
        ];

        foreach ($validCnpjs as $cnpj) {
            $this->assertTrue(CnpjHelper::isValid($cnpj), "CNPJ '{$cnpj}' deveria ser válido.");
        }
    }
}
