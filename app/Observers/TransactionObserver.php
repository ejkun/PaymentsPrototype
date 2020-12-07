<?php

namespace App\Observers;

use App\Exceptions\InsufficientFunds;
use App\Jobs\SendTransactionNotification;
use App\Models\Transaction;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        SendTransactionNotification::dispatch($transaction);
    }
}
