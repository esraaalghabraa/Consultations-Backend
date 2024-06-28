<?php

namespace App\Services;

use App\Exceptions\AuthenticationException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): User
    {
        return User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function login(array $data)
    {
        if (!auth()->validate($data)) {
            throw new AuthenticationException('The provided credentials are incorrect.');
        }

        return User::where('email', $data['email'])->first();
    }

    public function resetPassword($user, string $password): void
    {
        $user->update([
            'password' => Hash::make($password)
        ]);
        $user->save();
    }
}
