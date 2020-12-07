<?php

namespace Tests\Traits;

use App\Models\Transaction;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

trait HasNotificationHelpers
{
    protected function assertNotificationEmitted(Transaction $transaction) {
        Http::assertSent(function (Request $request) use ($transaction) {
            return $request->url() == config('services.transaction.notifier') &&
                $request['value'] == $transaction->value &&
                $request['payer'] == [
                    'id' => $transaction->payer->id,
                    'name' => $transaction->payer->name,
                ] &&
                $request['payee'] == [
                    'id' => $transaction->payee->id,
                    'name' => $transaction->payee->name,
                ];
        });
    }
}
