<?php

namespace Alhames\Api\Authentication;

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
     */
    public function authenticate(array $options = []): array;

    /**
     * @return int|string
     */
    public function getAccountId();
}
