<?php

namespace Tests\Feature\Controller;

use App\Exceptions\InsufficientFundsException;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private User $payee;
    private User $payer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payer = User::factory()->create([
            'type' => User::REGULAR
        ]);
        $this->payee = User::factory()->create();
    }

    public function testApiIndexTransactions()
    {
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

    public function testApiStoresATransaction()
    {
        $value = $this->faker->randomFloat(2, 1, $this->payer->balance);

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

        $this->assertInstanceOf(InsufficientFundsException::class, $response->exception);
        $this->assertDatabaseMissing('transactions', array_merge(
            ['id' => $response->json('id')],
            $data
        ));
    }
}
