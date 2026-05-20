<?php

namespace Espo\Custom\Classes\Mail;

final class Microsoft365OAuthAccountMap
{
    private const MAP = [
        'm365lamar0000001' => 'm365oauthlamar001',
        'm365gretch000001' => 'm365oauthgretch01',
        'googlelamar00001' => 'googleoauthlamar1',
    ];

    public function getOAuthAccountId(?string $emailAccountId): ?string
    {
        if (!$emailAccountId) {
            return null;
        }

        return self::MAP[$emailAccountId] ?? null;
    }
}
