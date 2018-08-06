<?php

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
}
