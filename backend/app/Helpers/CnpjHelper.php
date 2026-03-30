<?php

declare(strict_types=1);

namespace App\Helpers;

class CnpjHelper
{
    public static function sanitize(?string $cnpj): string
    {
        return preg_replace('/[^0-9]/', '', $cnpj ?? '');
    }

    public static function isValid(?string $cnpj): bool
    {
        $cnpj = self::sanitize($cnpj);

        if (strlen($cnpj) !== 14) {
            return false;
        }

        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        $calcDigit = function (string $cnpj, int $length): int {
            $weights = $length === 12
                ? [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
                : [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

            $sum = 0;
            for ($i = 0; $i < $length; $i++) {
                $sum += (int) $cnpj[$i] * $weights[$i];
            }

            $remainder = $sum % 11;

            return $remainder < 2 ? 0 : 11 - $remainder;
        };

        if ($calcDigit($cnpj, 12) !== (int) $cnpj[12]) {
            return false;
        }

        if ($calcDigit($cnpj, 13) !== (int) $cnpj[13]) {
            return false;
        }

        return true;
    }
}
