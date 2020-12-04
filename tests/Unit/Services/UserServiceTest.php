<?php

namespace Tests\Unity\Services\UserServiceTest;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(UserService::class);
    }

    public function testItIndexUsers()
    {
        $users = User::factory()->count(5)->create();

        $indexedUsers = $this->service->index();

        $this->assertEquals($users->toArray(), $indexedUsers->toArray());
    }

    public function testItStoresAUser()
    {
        $userData = User::factory()->definition();

        $user = $this->service->store($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', $user->toArray());
    }

    public function testItUpdatesAUser()
    {
        $user = User::factory()->create();
        $data = User::factory()->make()
            ->toArray();

        unset($data['email_verified_at']);

        $updated = $this->service->update($user, $data);

        $this->assertTrue($updated);
        $this->assertDatabaseHas('users', array_merge(
            ['id' => $user->id],
            $data
        ));
    }

    public function testItDestroysAUser()
    {
        $user = User::factory()->create();

        $deleted = $this->service->destroy($user);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('users', $user->toArray());
    }
}
