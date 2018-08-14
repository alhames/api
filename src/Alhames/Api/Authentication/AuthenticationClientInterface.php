<?php

namespace Alhames\Api\Authentication;

use Alhames\Api\Exception\ApiExceptionInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Interface AuthenticationClientInterface.
 */
interface AuthenticationClientInterface
{
    /**
     * @param string|null $state
     * @param array       $options
     *
     * @return string
     */
    public function getAuthenticationUri(?string $state = null, array $options = []): string;

    /**
     * @param array $options
     *
     * @return array
     * @throws ApiExceptionInterface
     * @throws GuzzleException
     */
    public function authenticate(array $options = []): array;

    /**
     * @return int|string
     * @throws ApiExceptionInterface
     * @throws GuzzleException
     */
    public function getAccountId();
}
