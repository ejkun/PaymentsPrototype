<?php

namespace Tests\Unit\Services\UserServiceTest;

use App\Exceptions\InsufficientFunds;
use App\Exceptions\InvalidPayerType;
use App\Exceptions\NotificationNotSent;
use App\Exceptions\TransactionRejectedByApprover;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Tests\Traits\HasHTTPFakers;
use Tests\Traits\HasNotificationHelpers;

class TransactionServiceTest extends TestCase
{
    use HasHTTPFakers;
    use HasNotificationHelpers;
    use RefreshDatabase;
    use WithFaker;

    private TransactionService $service;
    private User $payee;
    private User $payer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payer = User::factory()->create([
            'type' => User::REGULAR,
            'balance' => $this->faker->randomFloat(2, 0.01)
        ]);
        $this->payee = User::factory()->create();
        $this->service = app(TransactionService::class);
    }

    public function testItIndexTransactions()
    {
        $this->fakeApproverReturnedApproved();
        $this->fakeNotifierReturnedSent();

        $transactions = Transaction::factory()->count(5)->create([
            'payer_id' => $this->payer->id,
            'payee_id' => $this->payee->id
        ]);

        $this->assertEquals(
            $this->service->index()->toArray(),
            $transactions->toArray()
        );
    }

    public function testItStoresATransactionAndNotifies()
    {
        $this->fakeNotifierReturnedSent();
        $this->fakeApproverReturnedApproved();

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

        $this->assertNotificationEmitted($transaction);
    }

    public function testItThrowsInsufficientFundsException()
    {
        $value = $this->payer->balance + 1;

        try {
            $this->service->store($this->payer, $this->payee, $value);
        } catch (InsufficientFunds $exception) {
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
        $this->fakeApproverReturnedNotApproved();

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

    public function testItThrowsTransactionFailedToBeApproved()
    {
        $this->fakeApproverReturnedError();

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

    public function testItThrowsTransactionFailedToBeNotified()
    {
        $this->fakeApproverReturnedError();

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

    public function testItThrowsNotificationNotSent()
    {
        $this->fakeNotifierReturnedNotSent();
        $this->fakeApproverReturnedApproved();

        $value = $this->faker->randomFloat(2, 0.01, $this->payer->balance);

        try {
            $this->service->store($this->payer, $this->payee, $value);
        } catch (NotificationNotSent $exception) {
            $this->assertTrue(true, 'This line was not called');
        }

        $this->assertDatabaseMissing('transactions', [
            'payer_id' => $this->payer->id,
            'payee_id' => $this->payee->id,
            'value' => $value
        ]);
    }

    public function testItThrowsInvalidPayerType()
    {
        $payer = User::factory()->create([
            'type' => User::MERCHANT,
            'balance' => $this->faker->randomFloat(2, 0.01)
        ]);

        $value = $this->faker->randomFloat(2, 0.01, $payer->balance);

        try {
            $this->service->store($payer, $this->payee, $value);
        } catch (InvalidPayerType $exception) {
            $this->assertTrue(true, 'This line was not called');
        }

        $this->assertDatabaseMissing('transactions', [
            'payer_id' => $this->payer->id,
            'payee_id' => $this->payee->id,
            'value' => $value
        ]);
    }

    public function testItThrowsTransactionRejectedByApproverOnUnknownError()
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
