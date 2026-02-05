<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;

class XuiUser implements Authenticatable
{
    protected array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->attributes['id'] ?? null;
    }

    public function getAuthPassword(): string
    {
        return $this->attributes['password'] ?? '';
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
    }

    public function getRememberTokenName(): ?string
    {
        return null;
    }

    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __isset($key): bool
    {
        return isset($this->attributes[$key]);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function isAdmin(): bool
    {
        return isset($this->attributes['member_group_id']) && $this->attributes['member_group_id'] == 1;
    }

    public function isReseller(): bool
    {
        return isset($this->attributes['member_group_id']) && $this->attributes['member_group_id'] == 2;
    }

    public function getCredits(): float
    {
        return (float) ($this->attributes['credits'] ?? 0);
    }

    public function setCredits(float $amount): void
    {
        $this->attributes['credits'] = $amount;
    }
}
