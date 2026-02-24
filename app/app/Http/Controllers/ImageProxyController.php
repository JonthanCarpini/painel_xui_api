<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ImageProxyController extends Controller
{
    public function proxy()
    {
        $url = request()->query('url');

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            abort(404);
        }

        if ($this->isBlockedUrl($url)) {
            abort(403);
        }

        $cacheKey = 'img_proxy_' . md5($url);

        $data = Cache::remember($cacheKey, 3600, function () use ($url) {
            try {
                $response = Http::timeout(10)->get($url);

                if (!$response->successful()) {
                    return null;
                }

                $contentType = $response->header('Content-Type') ?? 'image/jpeg';
                if (!str_starts_with($contentType, 'image/')) {
                    return null;
                }

                return [
                    'body' => base64_encode($response->body()),
                    'content_type' => $contentType,
                ];
            } catch (\Exception $e) {
                return null;
            }
        });

        if (!$data) {
            abort(404);
        }

        return response(base64_decode($data['body']))
            ->header('Content-Type', $data['content_type'])
            ->header('Cache-Control', 'public, max-age=86400');
    }

    private function isBlockedUrl(string $url): bool
    {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';

        if (empty($host)) {
            return true;
        }

        $blockedHosts = ['localhost', '127.0.0.1', '0.0.0.0', '[::1]', '169.254.169.254'];
        if (in_array(strtolower($host), $blockedHosts)) {
            return true;
        }

        $ip = gethostbyname($host);
        if ($ip === $host && !filter_var($host, FILTER_VALIDATE_IP)) {
            return true;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return true;
        }

        return false;
    }
}
