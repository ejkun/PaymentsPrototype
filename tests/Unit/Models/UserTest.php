<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testUserModelFiltersDocument()
    {
        $cpf = $this->faker->cpf();

        $user = User::factory()->create([
            'document' => $cpf
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'document' => preg_replace('/\D/', '', $cpf)
        ]);
    }
}
