<?php

namespace Tests\Unit;

use App\Helpers\CpfHelper;
use PHPUnit\Framework\TestCase;

class CpfHelperTest extends TestCase
{
    // -------------------------------------------------------------------------
    // sanitize()
    // -------------------------------------------------------------------------

    public function test_sanitize_removes_mask(): void
    {
        $this->assertSame('52998224725', CpfHelper::sanitize('529.982.247-25'));
    }

    public function test_sanitize_removes_spaces_and_symbols(): void
    {
        $this->assertSame('52998224725', CpfHelper::sanitize('529 982 247 25'));
    }

    public function test_sanitize_returns_digits_only_when_already_clean(): void
    {
        $this->assertSame('52998224725', CpfHelper::sanitize('52998224725'));
    }

    public function test_sanitize_handles_null(): void
    {
        $this->assertSame('', CpfHelper::sanitize(null));
    }

    public function test_sanitize_handles_empty_string(): void
    {
        $this->assertSame('', CpfHelper::sanitize(''));
    }

    // -------------------------------------------------------------------------
    // isValid()
    // -------------------------------------------------------------------------

    public function test_is_valid_returns_true_for_valid_cpf(): void
    {
        $this->assertTrue(CpfHelper::isValid('52998224725'));
    }

    public function test_is_valid_accepts_masked_cpf(): void
    {
        $this->assertTrue(CpfHelper::isValid('529.982.247-25'));
    }

    public function test_is_valid_returns_false_for_wrong_first_digit(): void
    {
        // último dígito verificador alterado
        $this->assertFalse(CpfHelper::isValid('52998224715'));
    }

    public function test_is_valid_returns_false_for_wrong_second_digit(): void
    {
        // penúltimo dígito verificador alterado
        $this->assertFalse(CpfHelper::isValid('52998224726'));
    }

    public function test_is_valid_returns_false_for_all_same_digits(): void
    {
        foreach (range(0, 9) as $digit) {
            $cpf = str_repeat((string) $digit, 11);
            $this->assertFalse(CpfHelper::isValid($cpf), "CPF '{$cpf}' deveria ser inválido.");
        }
    }

    public function test_is_valid_returns_false_for_too_short_cpf(): void
    {
        $this->assertFalse(CpfHelper::isValid('1234567890'));
    }

    public function test_is_valid_returns_false_for_too_long_cpf(): void
    {
        $this->assertFalse(CpfHelper::isValid('529982247250'));
    }

    public function test_is_valid_returns_false_for_empty_string(): void
    {
        $this->assertFalse(CpfHelper::isValid(''));
    }

    public function test_is_valid_with_multiple_valid_cpfs(): void
    {
        $validCpfs = [
            '361.980.260-27',
            '642.346.230-59',
            '400.006.750-83',
            '089.196.370-75',
        ];

        foreach ($validCpfs as $cpf) {
            $this->assertTrue(CpfHelper::isValid($cpf), "CPF '{$cpf}' deveria ser válido.");
        }
    }
}
