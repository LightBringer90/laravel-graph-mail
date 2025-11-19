<?php

namespace ProgressiveStudios\GraphMail\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use ProgressiveStudios\GraphMail\Support\GraphLogger;

class GraphMailService
{
    protected string $base;

    public function __construct()
    {
        $this->base = rtrim((string) config('graph-mail.base'), '/');
    }

    /**
     * Prepare an authenticated HTTP client for Microsoft Graph.
     */
    protected function auth()
    {
        return Http::withToken(app(GraphToken::class)->appToken())
            ->acceptJson();
    }

    /**
     * Send a direct message via Microsoft Graph.
     *
     * This uses the /users/{id | userPrincipalName}/sendMail endpoint.
     *
     * @param  string  $senderUpn  UPN/email of the sender.
     * @param  array   $message    Payload in Graph sendMail format:
     *                             [
     *                               'message' => [...],
     *                               'saveToSentItems' => true|false,
     *                             ]
     *
     * @return array        Response JSON (Graph usually returns an empty body for sendMail).
     *
     * @throws RequestException on non-2xx responses.
     */
    public function send(string $senderUpn, array $message): array
    {
        $url = $this->base . '/users/' . urlencode($senderUpn) . '/sendMail';

        GraphLogger::channel()->info('graph.send', [
            'sender'  => $senderUpn,
            'payload' => $message,
        ]);

        $response = $this->auth()->post($url, $message)->throw();

        return $response->json() ?? [];
    }
}
