<?php

/*
 * This file is part of the Common API Interface package.
 *
 * (c) Pavel Logachev <alhames@mail.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alhames\Api\Authentication;

use Alhames\Api\AbstractApiClient;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class AbstractAuthenticationClient.
 */
abstract class AbstractAuthenticationClient extends AbstractApiClient implements AuthenticationClientInterface
{
    /** @var string */
    protected $redirectUri;
    /** @var int|string */
    protected $accountId;

    /**
     * {@inheritdoc}
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @param GuzzleException $exception
     *
     * @return GuzzleException|\Throwable
     */
    protected function handleApiException(GuzzleException $exception)
    {
        return $exception;
    }

    /**
     * Authorization Endpoint for OAuth2 authorization
     * or OP Endpoint URL for OpenID2 authentication.
     *
     * @see https://tools.ietf.org/html/rfc6749#section-3.1
     * @see https://openid.net/specs/openid-authentication-2_0.html#terminology
     *
     * @return string
     */
    abstract protected function getAuthEndpoint(): string;
}
