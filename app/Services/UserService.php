<?php

namespace App\Services;


use App\Exceptions\ExistObjectException;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserService
{
    public function getAuthenticatedUser()
    {
        $user = Auth::user();
        if ($user){
            throw new ExistObjectException('user not found');
        }
        return $user;
    }

    public function getAuthenticatedExpert()
    {
        $user= $this->getAuthenticatedUser();
        $user = User::with('expert')->find($user->id);
        $expert = $user->expert;
        if ($user || $expert){
            throw new ExistObjectException('expert not found');
        }
        return $expert;
    }
    public function updateUser($user, $request, $image)
    {
        $user->update([
            'image' => $image,
            'gender' => $request->gender,
            'birthdate' => $request->birthdate,
        ]);
    }
}
