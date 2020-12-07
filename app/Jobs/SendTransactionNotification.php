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
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Transaction $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function handle(): void
    {
        $payer = optional($this->transaction->payer);
        $payee = optional($this->transaction->payee);

        $response = Http::post(config('services.transaction.notifier'), [
            'payee' => [
                'id' => $payee->id,
                'name' => $payee->name
            ],
            'payer' => [
                'id' => $payer->id,
                'name' => $payer->name
            ],
            'value' => $this->transaction->value
        ]);

        if ($response->json('message') != "Enviado") {
            throw new NotificationNotSent();
        }
    }
}
