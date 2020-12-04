<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class OneOf implements Rule
{
    private array $rules;

    private string $message;

    /**
     * Create a new rule instance.
     *
     * @param string $message
     * @param Rule[] $rules
     */
    public function __construct(string $message, Rule ...$rules)
    {
        $this->rules = $rules;
        $this->message = $message;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $result = false;

        array_walk($this->rules, function (Rule $rule) use (&$result, $attribute, $value) {
            $result |= $rule->passes($attribute, $value);
        });

        return $result;
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
