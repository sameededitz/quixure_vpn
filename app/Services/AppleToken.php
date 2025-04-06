<?php

namespace App\Services;

use Firebase\JWT\JWT;

class AppleToken
{
    public function generateClientSecret(): string
    {
        $teamId = config('services.apple.team_id');
        $clientId = config('services.apple.client_id');
        $keyId = config('services.apple.key_id');
        $privateKey = config('services.apple.private_key');

        $header = [
            'alg' => 'ES256',
            'kid' => $keyId
        ];

        $claims = [
            'iss' => $teamId,
            'iat' => time(),
            'exp' => time() + 3600, // Token valid for 1 hour
            'aud' => 'https://appleid.apple.com',
            'sub' => $clientId,
        ];

        $privateKeyFormatted = "-----BEGIN PRIVATE KEY-----\n" . chunk_split($privateKey, 64, "\n") . "-----END PRIVATE KEY-----";
        return JWT::encode($claims, $privateKeyFormatted, 'ES256', $keyId);
    }
}