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

        $cacheKey = 'img_proxy_' . md5($url);

        $data = Cache::remember($cacheKey, 3600, function () use ($url) {
            try {
                $response = Http::timeout(10)->get($url);

                if (!$response->successful()) {
                    return null;
                }

                return [
                    'body' => base64_encode($response->body()),
                    'content_type' => $response->header('Content-Type') ?? 'image/jpeg',
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
}
