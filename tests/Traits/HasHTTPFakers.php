<?php

namespace Tests\Traits;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

trait HasHTTPFakers
{
    protected function fakeNotifierResponse(PromiseInterface $response): self
    {
        Http::fake([
            config('services.transaction.notifier') => $response
        ]);

        return $this;
    }

    protected function fakeNotifierReturnedSent(): self
    {
        return $this->fakeNotifierResponse(Http::response([
            'message' => 'Enviado'
        ]));
    }

    protected function fakeNotifierReturnedNotSent(): self
    {
        return $this->fakeNotifierResponse(Http::response([
            'message' => 'Não enviado'
        ]));
    }

    protected function fakeNotifierReturnedError(): self
    {
        return $this->fakeNotifierResponse(Http::response(null, Response::HTTP_INTERNAL_SERVER_ERROR));
    }

    protected function fakeApproverResponse(PromiseInterface $response): self
    {
        Http::fake([
            config('services.transaction.approver') => $response
        ]);

        return $this;
    }

    protected function fakeApproverReturnedApproved(): self
    {
        return $this->fakeApproverResponse(Http::response([
            'message' => 'Autorizado'
        ]));
    }

    protected function fakeApproverReturnedNotApproved(): self
    {
        return $this->fakeApproverResponse(Http::response([
            'message' => 'Não autorizado'
        ]));
    }

    protected function fakeApproverReturnedError(): self
    {
        return $this->fakeApproverResponse(Http::response(null, Response::HTTP_INTERNAL_SERVER_ERROR));
    }
}
