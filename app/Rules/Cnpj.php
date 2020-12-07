<?php

namespace App\Rules;

use App\Traits\HasRemoveNumbers;
use Illuminate\Contracts\Validation\Rule;

class Cnpj implements Rule
{
    use HasRemoveNumbers;

    /**
     * Determine if the validation rule passes.
     *
     * Based on https://www.geradorcnpj.com/script-validar-cnpj-php.htm
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value)
    {
        $cnpj = $this->removeNumbers($value);

        if (
            strlen($cnpj) != 14 ||
            preg_match('/(\d)\1{13}/', $cnpj)
        ) {
            return false;
        }

        $firstSum = 0;
        $secondSum = 0;

        for ($i = 0, $j = 5, $k = 6; $i < 12; $i++) {
            $j = $j == 1 ? 9 : $j;
            $k = $k == 1 ? 9 : $k;

            $secondSum += intval($cnpj[$i]) * $k;
            $firstSum += intval($cnpj[$i]) * $j;

            $k--;
            $j--;
        }

        $secondSum += intval($cnpj[12]) * 2;

        $firstRemainder = $firstSum % 11;
        $firstDigit = $firstRemainder < 2 ? 0 : 11 - $firstRemainder;

        $secondRemainder = $secondSum % 11;
        $secondDigit = $secondRemainder < 2 ? 0 : 11 - $secondRemainder;

        return (($cnpj[12] == $firstDigit) and ($cnpj[13] == $secondDigit));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The field :attribute is not a valid CNPJ';
    }
}
