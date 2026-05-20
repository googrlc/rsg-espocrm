<?php

namespace Espo\Custom\Classes\Mail;

use Espo\Core\Mail\Smtp\Handler;
use Espo\Core\Mail\SmtpParams;
use Espo\Tools\OAuth\Exceptions\OAuthException;
use Espo\Tools\OAuth\TokensProvider;
use RuntimeException;

class Microsoft365SmtpOAuthHandler implements Handler
{
    public function __construct(
        private TokensProvider $tokensProvider,
        private Microsoft365OAuthAccountMap $accountMap,
    ) {}

    public function handle(SmtpParams $params, ?string $id): SmtpParams
    {
        $oauthAccountId = $this->accountMap->getOAuthAccountId($id);

        if (!$oauthAccountId) {
            throw new RuntimeException("No OAuth account mapping for email account $id.");
        }

        try {
            $tokens = $this->tokensProvider->get($oauthAccountId);
        } catch (OAuthException $e) {
            throw new RuntimeException("Microsoft 365 OAuth token unavailable for email account $id.", 0, $e);
        }

        return $params
            ->withAuthMechanism(SmtpParams::AUTH_MECHANISM_XOAUTH)
            ->withPassword($tokens->getAccessToken());
    }
}
