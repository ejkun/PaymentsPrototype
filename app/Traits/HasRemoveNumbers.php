<?php

namespace App\Traits;

trait HasRemoveNumbers
{
    protected function removeNumbers(string $value): string
    {
        return (string) preg_replace('/[^0-9]/', '', $value);
    }
}
