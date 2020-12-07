<?php

namespace App\Services;

use App\Exceptions\InsufficientFunds;
use App\Exceptions\InvalidPayerType;
use App\Exceptions\TransactionRejectedByApprover;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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
     * @throws \Throwable
     */
    public function store(User $payer, User $payee, float $value): Transaction
    {
        if ($payer->balance < $value) {
            throw new InsufficientFunds();
        }

        if ($payer->type != User::REGULAR) {
            throw new InvalidPayerType();
        }

        if (!$this->checkApprover()) {
            throw new TransactionRejectedByApprover();
        }

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

    protected function checkApprover(): bool
    {
        try {
            $response = Http::acceptJson()->get(config('services.transaction.approver'));

            return $response->successful() && $response->json('message') == 'Autorizado';
        } catch (\Throwable $exception) {
            return false;
        }
    }
}
