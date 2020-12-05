<?php

namespace Tests\Feature\Controller;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testApiIndexUsers()
    {
        $users = User::factory()->count(5)->create();

        $response = $this->getJson(route('users.index'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $users->toArray()
            ]);
    }

    public function testApiShowsAUser()
    {
        $user = User::factory()->create();

        $response = $this->getJson(route('users.show', ['user' => $user->id]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $user->toArray()
            ]);
    }

    public function testApiStoresAUser()
    {
        $password = $this->faker->password;
        $user = User::factory()->make([])
            ->toArray();
        unset($user['balance']);

        $user['password'] = $password;
        $user['password_confirmation'] = $password;

        $response = $this->postJson(route('users.store'), $user);

        unset($user['password'], $user['password_confirmation']);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson($user);

        $this->assertDatabaseHas('users', array_merge(
            ['id' => $response->json('id')],
            $user
        ));
    }

    public function testApiUpdatesAUser()
    {
        $password = $this->faker->password;
        $cryptedPassword = Hash::make($password);
        $user = User::factory()->create([
            'password' => $cryptedPassword
        ]);

        $newName = $this->faker->name;

        $response = $this
            ->actingAs($user)
            ->putJson(
                route('users.update', ['user' => $user->id]),
                [
                    'name' => $newName,
                    'current_password' => $password
                ]
            );

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $newName
        ]);
    }

    public function testApiDeletesAUser()
    {
        $password = $this->faker->password;
        $cryptedPassword = Hash::make($password);
        $user = User::factory()->create([
            'password' => $cryptedPassword
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson(
                route('users.destroy', ['user' => $user->id]),
                [
                    'current_password' => $password
                ]
            );

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }
}