<?php

namespace App\Auth;

use App\Services\XuiApiService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class XuiUserProvider implements UserProvider
{
    protected XuiApiService $xuiApi;

    public function __construct(XuiApiService $xuiApi)
    {
        $this->xuiApi = $xuiApi;
    }

    public function retrieveById($identifier)
    {
        $response = $this->xuiApi->getUser($identifier);

        if (isset($response['data']) && is_array($response['data'])) {
            return new XuiUser($response['data']);
        }

        return null;
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

        $user = $this->xuiApi->authenticateUser(
            $credentials['username'],
            $credentials['password'] ?? ''
        );

        if ($user) {
            return new XuiUser($user);
        }

        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $user->getAuthPassword() === ($credentials['password'] ?? '');
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
    }
}
