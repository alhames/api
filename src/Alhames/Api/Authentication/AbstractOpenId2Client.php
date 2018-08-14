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

use Alhames\Api\Exception\AuthenticationException;
use Alhames\Api\HttpInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class OpenId2AuthenticationClient.
 *
 * @see https://openid.net/specs/openid-authentication-2_0.html
 */
abstract class AbstractOpenId2Client extends AbstractAuthenticationClient
{
    const OPEN_ID_IDENTIFIER = 'http://specs.openid.net/auth/2.0/identifier_select';
    const OPEN_ID_NS = 'http://specs.openid.net/auth/2.0';
    protected static $openIdRequiredFields = ['ns', 'mode', 'op_endpoint', 'return_to', 'response_nonce', 'assoc_handle', 'signed', 'sig'];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        if (empty($config['redirect_uri'])) {
            throw new \InvalidArgumentException('Option "redirect_uri" is required.');
        }
        $this->redirectUri = $config['redirect_uri'];
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthenticationUri(?string $state = null, array $options = []): string
    {
        $redirectUri = $this->getRedirectUriWithState($state);
        $query = [
            'openid.ns' => self::OPEN_ID_NS,
            'openid.mode' => 'checkid_setup',
            'openid.identity' => $options['identity'] ?? self::OPEN_ID_IDENTIFIER,
            'openid.claimed_id' => $options['claimed_id'] ?? $options['identity'] ?? self::OPEN_ID_IDENTIFIER,
            'openid.return_to' => $redirectUri,
            'openid.realm' => parse_url($redirectUri, PHP_URL_SCHEME).'://'.parse_url($redirectUri, PHP_URL_HOST),
        ];

        return $this->getAuthEndpoint().'?'.http_build_query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(array $options = []): array
    {
        $query = [];
        foreach (self::$openIdRequiredFields as $field) {
            if (isset($options['openid_'.$field])) {
                $query['openid.'.$field] = $options['openid_'.$field];
            } elseif (isset($options['openid.'.$field])) {
                $query['openid.'.$field] = $options['openid.'.$field];
            } else {
                throw AuthenticationException::invalidArgument($field, $options);
            }
        }

        if (self::OPEN_ID_NS !== $query['openid.ns']) {
            throw AuthenticationException::invalidArgument('ns', $options);
        }

        if ('id_res' === $query['openid.mode']) {
            $query['openid.mode'] = 'check_authentication';
        } else {
            throw AuthenticationException::invalidArgument('mode', $options);
        }

        if ($this->getAuthEndpoint() !== $query['openid.op_endpoint']) {
            throw AuthenticationException::invalidArgument('op_endpoint', $options);
        }

        if ($this->getRedirectUriWithState($options['state'] ?? null) !== $query['openid.return_to']) {
            throw AuthenticationException::invalidArgument('return_to', $options);
        }

        if (isset($options['openid_claimed_id'], $options['openid_identity'])) {
            $query['openid.claimed_id'] = $options['openid_claimed_id'];
            $query['openid.identity'] = $options['openid_identity'];
        } elseif (isset($options['openid.claimed_id'], $options['openid.identity'])) {
            $query['openid.claimed_id'] = $options['openid.claimed_id'];
            $query['openid.identity'] = $options['openid.identity'];
        }

        try {
            $data = $this->httpClient->requestKeyValueForm(HttpInterface::METHOD_POST, $this->getAuthEndpoint(), $query);
        } catch (GuzzleException $e) {
            throw $this->handleApiException($e);
        }
        if (!isset($data['ns'], $data['is_valid']) || self::OPEN_ID_NS !== $data['ns'] || 'true' !== $data['is_valid']) {
            throw new AuthenticationException($data, 'OpendID authentication is failed.');
        }

        return array_merge($query, $data);
    }

    /**
     * @param string|null $state
     *
     * @return string
     */
    private function getRedirectUriWithState(?string $state = null): string
    {
        if (null === $state) {
            return $this->redirectUri;
        }

        $sep = false === strpos($this->redirectUri, '?') ? '?' : '&';

        return $this->redirectUri.$sep.'state='.$state;
    }
}
