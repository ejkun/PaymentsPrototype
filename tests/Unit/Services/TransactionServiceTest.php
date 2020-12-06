<?php

namespace Tests\Unity\Services\UserServiceTest;

use App\Exceptions\InsufficientFundsException;
use App\Exceptions\TransactionRejectedByApprover;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private TransactionService $service;
    private User $payee;
    private User $payer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payer = User::factory()->create([
            'type' => User::REGULAR
        ]);
        $this->payee = User::factory()->create();
        $this->service = app(TransactionService::class);
    }

    public function testItIndexTransactions()
    {
        $transactions = Transaction::factory()->count(5)->create([
            'payer_id' => $this->payer->id,
            'payee_id' => $this->payee->id
        ]);

        $this->assertEquals(
            $this->service->index()->toArray(),
            $transactions->toArray()
        );
    }

    public function testItStoresATransaction()
    {
        $value = $this->faker->randomFloat(2, 0.01, $this->payer->balance);

        $transaction = $this->service->store($this->payer, $this->payee, $value);

        $expectedData = [
            'payer_id' => $this->payer->id,
            'payee_id' => $this->payee->id,
            'value' => $value
        ];

        $this->assertInstanceOf(Transaction::class, $transaction);

        array_walk($expectedData, function ($value, $key) use ($transaction) {
            $this->assertEquals($value, $transaction->$key);
        });
        $this->assertDatabaseHas('transactions', $expectedData);
    }

    public function testItThrowsInsufficientFundsException()
    {
        $value = $this->payer->balance + 1;

        try {
            $this->service->store($this->payer, $this->payee, $value);
        } catch (InsufficientFundsException $exception) {
            $this->assertTrue(true, 'This line was not called');
        }

        $this->assertDatabaseMissing('transactions', [
            'payer_id' => $this->payer->id,
            'payee_id' => $this->payee->id,
            'value' => $value
        ]);
    }

    public function testItThrowsTransactionRejectedByApprover()
    {
        Config::set('services.transaction.approver', '');

        $value = $this->faker->randomFloat(2, 0.01, $this->payer->balance);

        try {
            $this->service->store($this->payer, $this->payee, $value);
        } catch (TransactionRejectedByApprover $exception) {
            $this->assertTrue(true, 'This line was not called');
        }

        $this->assertDatabaseMissing('transactions', [
            'payer_id' => $this->payer->id,
            'payee_id' => $this->payee->id,
            'value' => $value
        ]);
    }
}
