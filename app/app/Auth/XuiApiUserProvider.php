<?php

namespace App\Auth;

use App\Services\XuiApiService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

/**
 * Provider de autenticação que valida credenciais via API XUI.
 *
 * retrieveByCredentials → busca usuário pelo username via API (get_users)
 * validateCredentials   → valida senha localmente (md5 ou bcrypt), igual ao provider legado
 * retrieveById          → chama get_user na API para recarregar a sessão
 */
class XuiApiUserProvider implements UserProvider
{
    public function __construct(private XuiApiService $api) {}

    // -------------------------------------------------------------------------
    // Recuperar usuário por ID (recarregamento de sessão)
    // -------------------------------------------------------------------------

    public function retrieveById($identifier): ?Authenticatable
    {
        $response = $this->api->getUser((int)$identifier);

        if (($response['status'] ?? '') !== 'STATUS_SUCCESS') {
            return null;
        }

        $data = $response['data'] ?? [];

        if ((int)($data['status'] ?? 1) !== 1) {
            return null;
        }

        return new XuiApiUser($data);
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void {}

    // -------------------------------------------------------------------------
    // Recuperar usuário pelas credenciais (login)
    // -------------------------------------------------------------------------

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (empty($credentials['username'])) {
            return null;
        }

        $userData = $this->api->authenticateUser($credentials['username']);

        if ($userData === null) {
            return null;
        }

        return new XuiApiUser($userData);
    }

    // -------------------------------------------------------------------------
    // Validar senha (chamado após retrieveByCredentials)
    // Lógica idêntica ao XuiDatabaseUserProvider original:
    //   - MD5 de 32 chars hex → md5($password) === $hash
    //   - Hash bcrypt/argon   → password_verify()
    //   - Texto plano         → comparação direta
    // -------------------------------------------------------------------------

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        if (!isset($credentials['password'])) {
            return false;
        }

        $password       = $credentials['password'];
        $hashedPassword = $user->getAuthPassword();

        if (strlen($hashedPassword) === 32 && ctype_xdigit($hashedPassword)) {
            return md5($password) === $hashedPassword;
        }

        if (str_starts_with($hashedPassword, '$')) {
            // Suporte a SHA-512 crypt ($6$) usado pelo XUI
            if (str_starts_with($hashedPassword, '$6$')) {
                return crypt($password, $hashedPassword) === $hashedPassword;
            }
            return password_verify($password, $hashedPassword);
        }

        return $password === $hashedPassword;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void {}
}
