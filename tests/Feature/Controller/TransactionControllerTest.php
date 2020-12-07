<?php

namespace Tests\Feature\Controller;

use App\Exceptions\InsufficientFunds;
use App\Exceptions\TransactionRejectedByApprover;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;
use Tests\Traits\HasHTTPFakers;
use Tests\Traits\HasNotificationHelpers;

class TransactionControllerTest extends TestCase
{
    use HasHTTPFakers;
    use HasNotificationHelpers;
    use RefreshDatabase;
    use WithFaker;

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
    }

    public function testApiIndexTransactions()
    {
        $this->fakeApproverReturnedApproved();
        $this->fakeNotifierReturnedSent();

        $transactions = Transaction::factory()->count(5)->create([
            'payer_id' => $this->payer->id,
            'payee_id' => $this->payee->id
        ])->toArray();

        array_walk($transactions, function (array &$transaction) {
            unset($transaction['updated_at']);
        });

        $response = $this->getJson(route('transactions.index'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
            'data' => $transactions
        ]);
    }

    public function testApiShowsATransaction()
    {
        $this->fakeApproverReturnedApproved();
        $this->fakeNotifierReturnedSent();

        $transaction = Transaction::factory()->create([
            'payer_id' => $this->payer->id,
            'payee_id' => $this->payee->id
        ])->toArray();

        unset($transaction['updated_at']);

        $response = $this->getJson(route('transactions.show', [
            'transaction' => $transaction['id']
        ]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson($transaction);
    }

    public function testApiReturnsNotFoundWhenTransactionDoesNotExist()
    {
        $response = $this->getJson(route('transactions.show', [
            'transaction' => $this->faker->randomNumber()
        ]));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testApiStoresATransactionAndNotifies()
    {
        $this->fakeApproverReturnedApproved();
        $this->fakeNotifierReturnedSent();

        $value = $this->faker->randomFloat(2, 0.01, $this->payer->balance);

        $data = [
            'payer_id' => $this->payer->id,
            'payee_id' => $this->payee->id,
            'value' => $value
        ];

        $response = $this->postJson(
            route('transactions.store'),
            $data
        );

        $response->assertJson($data);
        $this->assertDatabaseHas('transactions', array_merge(
            ['id' => $response->json('id')],
            $data
        ));

        $this->assertNotificationEmitted(Transaction::findOrFail($response->json('id')));
    }

    public function testApiThrowsBadRequestWithInsufficientFunds()
    {
        $value = $this->payer->balance + 1;

        $data = [
            'payer_id' => $this->payer->id,
            'payee_id' => $this->payee->id,
            'value' => $value
        ];

        $response = $this->postJson(
            route('transactions.store'),
            $data
        );

        $response
            ->assertStatus(Response::HTTP_BAD_REQUEST);

        $this->assertInstanceOf(InsufficientFunds::class, $response->exception);
        $this->assertDatabaseMissing('transactions', $data);
    }

    public function testApiThrowsBadRequestWithTransactionRejectedByApprover()
    {
        $this->fakeApproverReturnedError();

        $value = $this->faker->randomFloat(2, 0.01, $this->payer->balance);

        $data = [
            'payer_id' => $this->payer->id,
            'payee_id' => $this->payee->id,
            'value' => $value
        ];

        $response = $this->postJson(
            route('transactions.store'),
            $data
        );

        $response
            ->assertStatus(Response::HTTP_BAD_REQUEST);

        $this->assertInstanceOf(TransactionRejectedByApprover::class, $response->exception);
        $this->assertDatabaseMissing('transactions', $data);
    }

    public function testApiThrowsBadRequestWithTransactionFailedToBeApproved()
    {
        $this->fakeApproverReturnedError();

        $value = $this->faker->randomFloat(2, 0.01, $this->payer->balance);

        $data = [
            'payer_id' => $this->payer->id,
            'payee_id' => $this->payee->id,
            'value' => $value
        ];

        $response = $this->postJson(
            route('transactions.store'),
            $data
        );

        $response
            ->assertStatus(Response::HTTP_BAD_REQUEST);

        $this->assertInstanceOf(TransactionRejectedByApprover::class, $response->exception);
        $this->assertDatabaseMissing('transactions', $data);
    }
}
