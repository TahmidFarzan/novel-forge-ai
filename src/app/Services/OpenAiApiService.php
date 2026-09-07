<?php
namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class OpenAiApiService
{
    protected int $defaultTimeout = 120;

    public function sendPostRequest(
        string $url,
        string $apiKey,
        string $model,
        mixed $data = null,
        ?int $maxOutputTokens = null,
        ?int $timeout = null
    ): array {

        $payload = $this->buildPayload(
            $model,
            $data,
            $maxOutputTokens
        );

        $response = Http::timeout(
            $timeout ?? $this->defaultTimeout
        )
            ->withHeaders([
                'Authorization'      => 'Bearer ' . $apiKey,
                'Content-Type'       => 'application/json',
                'HTTP-Referer'       => config('app.url'),
                'X-OpenRouter-Title' => config('app.name'),
            ])
            ->post(
                $url,
                $payload
            );

        if (! $response->successful()) {

            throw new Exception(
                $response->body()
            );

        }

        return $response->json();
    }

    public function sendGetRequest(
        string $url,
        string $apiKey,
        array $params = [],
        ?int $timeout = null
    ): array {

        $response = Http::timeout(
            $timeout ?? $this->defaultTimeout
        )
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])
            ->get(
                $url,
                $params
            );

        if (! $response->successful()) {

            throw new Exception(
                $response->body()
            );

        }

        return $response->json();
    }

    private function buildPayload(
        string $model,
        mixed $data = null,
        ?int $maxOutputTokens = null
    ): array {

        $content = '';

        if (is_string($data)) {

            $content = $data;

        } elseif (is_array($data)) {

            if (isset($data['content'])) {

                $content = $data['content'];

            } else {

                $content = json_encode($data);

            }

        }

        $payload = [

            'model'    => $model,

            'messages' => [
                [
                    'role'    => 'user',
                    'content' => $content,
                ],
            ],

        ];

        if (! empty($maxOutputTokens)) {

            $payload['max_tokens'] = $maxOutputTokens;

        }

        return $payload;
    }
}
