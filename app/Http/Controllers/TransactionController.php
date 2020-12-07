<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransaction;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    private TransactionService $service;

    public function __construct(TransactionService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return TransactionResource::collection(Transaction::all());
    }

    public function store(StoreTransaction $request)
    {
        return new JsonResponse(
            new TransactionResource(
                $this->service->store(
                    $request->payer,
                    $request->payee,
                    $request->validated()['value']
                )
            ),
            Response::HTTP_CREATED
        );
    }

    public function show(Transaction $transaction)
    {
        return new JsonResponse(
            new TransactionResource($transaction),
            Response::HTTP_OK
        );
    }
}
