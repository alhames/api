<?php

/*
 * This file is part of the Common API Interface package.
 *
 * (c) Pavel Logachev <alhames@mail.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * @throws ApiExceptionInterface
     * @throws GuzzleException
     *
     * @return mixed
     */
    public function request(string $method, array $query = [], string $httpMethod = HttpInterface::METHOD_GET);
}
