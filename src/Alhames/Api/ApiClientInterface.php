<?php

namespace Alhames\Api;

use Alhames\Api\Exception\ApiExceptionInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Interface ApiClientInterface.
 */
interface ApiClientInterface
{
    const VERSION = '0.1';

    /**
     * @param string $method
     * @param array  $query
     * @param string $httpMethod
     *
     * @return mixed
     * @throws ApiExceptionInterface
     * @throws GuzzleException
     */
    public function request(string $method, array $query = [], string $httpMethod = HttpInterface::METHOD_GET);
}
