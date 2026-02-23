<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    public function getUsdToBrl(): float
    {
        return Cache::remember('usd_brl_rate', 1800, function () {
            return $this->fetchUsdToBrl();
        });
    }

    protected function fetchUsdToBrl(): float
    {
        try {
            $response = Http::timeout(10)->get('https://economia.awesomeapi.com.br/json/last/USD-BRL');

            if ($response->successful()) {
                $data = $response->json();
                $rate = (float) ($data['USDBRL']['bid'] ?? 0);
                if ($rate > 0) {
                    return $rate;
                }
            }
        } catch (\Exception $e) {
            Log::warning('CurrencyService: Falha AwesomeAPI', ['error' => $e->getMessage()]);
        }

        try {
            $response = Http::timeout(10)->get('https://open.er-api.com/v6/latest/USD');

            if ($response->successful()) {
                $data = $response->json();
                $rate = (float) ($data['rates']['BRL'] ?? 0);
                if ($rate > 0) {
                    return $rate;
                }
            }
        } catch (\Exception $e) {
            Log::warning('CurrencyService: Falha er-api fallback', ['error' => $e->getMessage()]);
        }

        return 5.50;
    }

    public function convertUsdToBrl(float $usd, float $markupPercent = 0): array
    {
        $rate = $this->getUsdToBrl();
        $baseBrl = round($usd * $rate, 2);
        $markup = round($baseBrl * ($markupPercent / 100), 2);
        $finalBrl = round($baseBrl + $markup, 2);

        return [
            'usd' => $usd,
            'rate' => $rate,
            'base_brl' => $baseBrl,
            'markup_percent' => $markupPercent,
            'markup_brl' => $markup,
            'final_brl' => $finalBrl,
        ];
    }
}
