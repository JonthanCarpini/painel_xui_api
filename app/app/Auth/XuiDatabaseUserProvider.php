<?php

namespace App\Auth;

use App\Models\XuiUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

class XuiDatabaseUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        return XuiUser::where('id', $identifier)
            ->where('status', 1)
            ->first();
    }

    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (!isset($credentials['username'])) {
            return null;
        }

        return XuiUser::where('username', $credentials['username'])
            ->where('status', 1)
            ->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        if (!isset($credentials['password'])) {
            return false;
        }

        $password = $credentials['password'];
        $hashedPassword = $user->getAuthPassword();

        if (strlen($hashedPassword) === 32 && ctype_xdigit($hashedPassword)) {
            return md5($password) === $hashedPassword;
        }

        if (strpos($hashedPassword, '$') === 0) {
            return password_verify($password, $hashedPassword);
        }

        return $password === $hashedPassword;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
    }
}
