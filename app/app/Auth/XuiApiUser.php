<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;

use App\Models\PanelUser;

/**
 * Representa o usuário autenticado via API XUI.
 * Envolve o array retornado pela API e implementa Authenticatable
 * para ser compatível com o sistema de Auth do Laravel.
 */
class XuiApiUser implements Authenticatable
{
    private array $data;
    private ?PanelUser $panelUser = null; // Cache local do PanelUser

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    // ...

    public function getPreference($key, $default = null)
    {
        if ($this->panelUser === null) {
            $this->panelUser = PanelUser::where('xui_id', $this->getXuiIdAttribute())->first();
        }

        return $this->panelUser ? $this->panelUser->getPreference($key, $default) : $default;
    }

    // -------------------------------------------------------------------------
    // Acesso aos dados do array da API
    // -------------------------------------------------------------------------

    public function __get(string $key): mixed
    {
        // Alias: controllers migrados usam $user->xui_id para obter o ID do XUI
        if ($key === 'xui_id') {
            return (int)($this->data['id'] ?? 0);
        }

        return $this->data[$key] ?? null;
    }

    public function __isset(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function toArray(): array
    {
        return $this->data;
    }

    // -------------------------------------------------------------------------
    // Helpers de negócio (compatíveis com o que os controllers esperam)
    // -------------------------------------------------------------------------

    public function isAdmin(): bool
    {
        return (int)($this->data['member_group_id'] ?? 2) === 1;
    }

    public function isReseller(): bool
    {
        return (int)($this->data['member_group_id'] ?? 2) === 2;
    }

    public function getCredits(): float
    {
        return (float)($this->data['credits'] ?? 0);
    }

    /**
     * xui_id é o ID do usuário no XUI — usado pelos controllers migrados.
     */
    public function getXuiIdAttribute(): int
    {
        return (int)($this->data['id'] ?? 0);
    }

    // -------------------------------------------------------------------------
    // Authenticatable interface
    // -------------------------------------------------------------------------

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->data['id'] ?? null;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getAuthPassword(): string
    {
        return $this->data['password'] ?? '';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void {}

    public function getRememberTokenName(): string
    {
        return '';
    }
}
