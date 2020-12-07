<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function index(): Collection
    {
        return User::all();
    }

    public function store(array $data): User
    {
        $this->cryptPassword($data);

        return User::create($data);
    }

    public function update(User $user, array $data): bool
    {
        $this->cryptPassword($data);

        return $user->update($data);
    }

    public function destroy(User $user): bool
    {
        return $user->delete();
    }

    private function cryptPassword(array &$data): void
    {
        if (array_key_exists('password', $data)) {
            $data['password'] = Hash::make($data['password']);
        }
    }
}
