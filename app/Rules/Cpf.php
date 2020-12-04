<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Cpf implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * Based on https://gist.github.com/guisehn/3276015
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $cpf = preg_replace('/[^0-9]/', '', (string) $value);

        if (strlen($cpf) != 11)
            return false;

        if (preg_match('/(\d)\1{10}/', $cpf))
            return false;

        for ($i = 0, $j = 10, $sum = 0; $i < 9; $i++, $j--)
            $sum += $cpf[$i] * $j;

        $remainder = $sum % 11;

        if ($cpf[9] != ($remainder < 2 ? 0 : 11 - $remainder))
            return false;

        for ($i = 0, $j = 11, $sum = 0; $i < 10; $i++, $j--)
            $sum += $cpf[$i] * $j;

        $remainder = $sum % 11;

        return $cpf[10] == ($remainder < 2 ? 0 : 11 - $remainder);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The data is not a valid CPF';
    }
}
