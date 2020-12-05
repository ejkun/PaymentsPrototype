<?php

namespace App\Services;

use App\Exceptions\InsufficientFundsException;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function index(): Collection
    {
        return Transaction::all();
    }

    /**
     * @param User $payer
     * @param User $payee
     * @param float $value
     * @return Transaction
     * @throws InsufficientFundsException
     * @throws \Throwable
     */
    public function store(User $payer, User $payee, float $value): Transaction
    {
        if ($payer->balance < $value) {
            throw new InsufficientFundsException();
        }

        $transaction = null;

        try {
            DB::beginTransaction();

            $transaction = Transaction::create([
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'value' => $value
            ]);

            $payer->update([
                'balance' => $payer->balance - $value
            ]);

            $payee->update([
                'balance' => $payee->balance + $value
            ]);

            DB::commit();

            return $transaction;
        } catch (\Throwable $throwable) {
            DB::rollBack();
            throw $throwable;
        }
    }
}
