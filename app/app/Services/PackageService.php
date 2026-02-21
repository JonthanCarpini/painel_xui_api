<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Encapsula acesso a pacotes e bouquets via API XUI.
 * Substitui os Models Package e Bouquet que dependiam de DB direto.
 * Cache de 1 hora para evitar chamadas repetidas.
 */
class PackageService
{
    public function __construct(private XuiApiService $api) {}

    /**
     * Retorna todos os pacotes como Collection de objetos.
     */
    public function all(): Collection
    {
        return Cache::remember('xui_packages_all', 3600, function () {
            $raw = $this->api->getPackages();
            return collect($raw)->map(fn($p) => $this->castPackage($p));
        });
    }

    /**
     * Busca um pacote pelo ID. Retorna objeto ou null.
     */
    public function find(int $id): ?object
    {
        return $this->all()->firstWhere('id', $id);
    }

    /**
     * Lança exceção se o pacote não for encontrado.
     */
    public function findOrFail(int $id): object
    {
        $package = $this->find($id);
        if (!$package) {
            throw new \Exception("Pacote #{$id} não encontrado na API XUI.");
        }
        return $package;
    }

    /**
     * Retorna pacotes filtrados por campo.
     */
    public function where(string $field, mixed $value): Collection
    {
        return $this->all()->where($field, $value);
    }

    /**
     * Retorna todos os bouquets como Collection de objetos.
     * Aplica blacklist configurada em xui.bouquet_blacklist.
     */
    public function bouquets(?array $blacklist = null): Collection
    {
        $blacklist = $blacklist ?? config('xui.bouquet_blacklist', []);

        return Cache::remember('xui_bouquets_all', 3600, function () {
            $raw = $this->api->getBouquets();
            return collect($raw)->map(fn($b) => (object) $b)->sortBy('bouquet_order');
        })->filter(fn($b) => !in_array((int)($b->id ?? 0), $blacklist))->values();
    }

    /**
     * Limpa o cache de pacotes e bouquets.
     */
    public function clearCache(): void
    {
        Cache::forget('xui_packages_all');
        Cache::forget('xui_bouquets_all');
    }

    /**
     * Converte array da API para objeto com os mesmos campos do Model Package.
     */
    private function castPackage(array $p): object
    {
        return (object) [
            'id'                    => (int) ($p['id'] ?? 0),
            'package_name'          => $p['package_name'] ?? '',
            'is_addon'              => (bool) ($p['is_addon'] ?? false),
            'is_official'           => (bool) ($p['is_official'] ?? false),
            'is_trial'              => (bool) ($p['is_trial'] ?? false),
            'official_credits'      => (float) ($p['official_credits'] ?? 0),
            'trial_credits'         => (float) ($p['trial_credits'] ?? 0),
            'official_duration'     => (int) ($p['official_duration'] ?? 0),
            'official_duration_in'  => $p['official_duration_in'] ?? 'months',
            'trial_duration'        => (int) ($p['trial_duration'] ?? 0),
            'trial_duration_in'     => $p['trial_duration_in'] ?? 'days',
            'max_connections'       => (int) ($p['max_connections'] ?? 1),
            'bouquets'              => is_string($p['bouquets'] ?? null)
                                        ? $p['bouquets']
                                        : json_encode($p['bouquets'] ?? []),
            'output_formats'        => is_string($p['output_formats'] ?? null)
                                        ? $p['output_formats']
                                        : json_encode($p['output_formats'] ?? [1, 2]),
            'force_server_id'       => (int) ($p['force_server_id'] ?? 0),
        ];
    }
}
