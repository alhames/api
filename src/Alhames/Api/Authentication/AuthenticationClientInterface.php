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
     * @throws ApiExceptionInterface
     * @throws GuzzleException
     *
     * @return array
     */
    public function authenticate(array $options = []): array;

    /**
     * @throws ApiExceptionInterface
     * @throws GuzzleException
     *
     * @return int|string
     */
    public function getAccountId();
}
