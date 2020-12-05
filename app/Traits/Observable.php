<?php

namespace App\Traits;

trait Observable
{
    public static function bootObservable(): void
    {
        static::observe(static::$observer);
    }
}
