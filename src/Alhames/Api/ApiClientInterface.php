<?php

namespace Alhames\Api;

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
     */
    public function request(string $method, array $query = [], string $httpMethod = HttpInterface::METHOD_GET);
}
