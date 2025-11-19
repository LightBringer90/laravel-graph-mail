<?php

namespace ProgressiveStudios\GraphMail\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GraphToken
{
    public function appToken(): string
    {
        return Cache::remember('graph_app_token', 3300, function () {
            $tenant = config('graph-mail.tenant_id');
            $id = config('graph-mail.client_id');
            $secret = config('graph-mail.client_secret');
            $resp = Http::asForm()->post("https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token", [
                'client_id'     => $id,
                'client_secret' => $secret,
                'scope'         => 'https://graph.microsoft.com/.default',
                'grant_type'    => 'client_credentials',
            ])->throw()->json();
            return $resp['access_token'];
        });
    }
}
