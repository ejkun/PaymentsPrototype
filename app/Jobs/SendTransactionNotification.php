<?php

namespace App\Jobs;

use App\Exceptions\NotificationNotSent;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendTransactionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Transaction $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function handle(): void
    {
        $response = Http::post(config('services.transaction.notifier'), [
            'payee' => [
                'id' => $this->transaction->payee->id,
                'name' => $this->transaction->payee->name
            ],
            'payer' => [
                'id' => $this->transaction->payer->id,
                'name' => $this->transaction->payer->name
            ],
            'value' => $this->transaction->value
        ]);

        if ($response->json('message') != "Enviado") {
            throw new NotificationNotSent();
        }
    }
}
