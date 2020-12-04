<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class UserService
{
    public function index(): Collection
    {
        return User::all();
    }

    public function store(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function destroy(User $user): bool
    {
        try {
            return $user->delete();
        } catch (\Exception $e) {
            return false;
        }
    }
}
