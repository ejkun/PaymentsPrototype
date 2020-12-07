<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class OneOf implements Rule
{
    private array $rules;

    private string $message;

    public function __construct(string $message, Rule ...$rules)
    {
        $this->rules = $rules;
        $this->message = $message;
    }

    public function passes($attribute, $value): bool
    {
        $result = false;

        array_walk($this->rules, function (Rule $rule) use (&$result, $attribute, $value) {
            $result |= $rule->passes($attribute, $value);
        });

        return (bool) $result;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
