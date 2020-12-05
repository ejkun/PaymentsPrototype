<?php

namespace App\Models;

use App\Observers\TransactionObserver;
use App\Traits\Observable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    use Observable;

    public static string $observer = TransactionObserver::class;

    public $timestamps = true;

    protected $fillable = [
        'payer_id',
        'payee_id',
        'value'
    ];

    protected $casts = [
        'value' => 'float',
        'created_at' => 'datetime'
    ];

    protected $hidden = [
        'created_at'
    ];
}
