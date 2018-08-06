<?php

namespace Alhames\Api\Authentication;

use Alhames\Api\Exception\AuthenticationException;
use Alhames\Api\Exception\InvalidArgumentException;
use Alhames\Api\HttpInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class OAuth2AuthenticationHelper.
 */
abstract class AbstractOAuth2Client extends AbstractAuthenticationClient
{
    /** @var string */
    protected $accessToken;
    /** @var string */
    protected $clientId;
    /** @var string */
    protected $clientSecret;
    /** @var array|string */
    protected $scope;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        if (empty($config['redirect_uri'])) {
            throw new \InvalidArgumentException('Option "redirect_uri" is required.');
        }
        if (empty($config['client_id'])) {
            throw new \InvalidArgumentException('Option "client_id" is required.');
        }
        if (empty($config['client_secret'])) {
            throw new \InvalidArgumentException('Option "client_secret" is required.');
        }

        $this->redirectUri = $config['redirect_uri'];
        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->accessToken = $config['access_token'] ?? null;
        $this->scope = $config['scope'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthenticationUri(?string $state = null, array $options = []): string
    {
        $query = array_merge($options, [
            'response_type' => 'code',
            'client_id' => $this->clientId,
        ]);

        if (!isset($query['redirect_uri'])) {
            $query['redirect_uri'] = $this->redirectUri;
        }

        if (!isset($query['scope']) && null !== $this->scope) {
            $query['scope'] = $this->scope;
        }

        if (isset($query['scope']) && is_array($query['scope'])) {
            $query['scope'] = implode(' ', $query['scope']);
        }

        if (null !== $state) {
            $query['state'] = $state;
        }

        return $this->getBaseAuthenticationUri().'?'.http_build_query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(array $options = []): array
    {
        if (empty($options['code']) || !is_string($options['code'])) {
            throw new InvalidArgumentException('code');
        }

        $query = [
            'grant_type' => 'authorization_code',
            'code' => $options['code'],
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $options['redirect_uri'] ?? $this->redirectUri,
        ];

        try {
            $data = (array) $this->httpClient->requestJson(HttpInterface::METHOD_POST, $this->getTokenUri(), null, $query);
        } catch (GuzzleException $e) {
            throw $this->handleApiException($e);
        }

        if (empty($data['access_token'])) {
            throw new AuthenticationException($data, 'OAuth2 authentication is failed.');
        }

        $this->setAccessToken($data['access_token']);

        return $data;
    }

    /**
     * @param null|string $accessToken
     *
     * @return static
     */
    public function setAccessToken(?string $accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public function request(string $method, array $query = [], string $httpMethod = HttpInterface::METHOD_GET)
    {
        $uri = $this->getApiUri($method);
        $get = HttpInterface::METHOD_GET === $httpMethod ? $query : null;
        $post = HttpInterface::METHOD_GET !== $httpMethod ? $query : null;
        $headers = null !== $this->accessToken ? [HttpInterface::HEADER_AUTHORIZATION => 'Bearer '.$this->accessToken] : null;

        try {
            return $this->httpClient->requestJson($httpMethod, $uri, $get, $post, null, $headers);
        } catch (GuzzleException $e) {
            throw $this->handleApiException($e);
        }
    }

    /**
     * @param string $method
     *
     * @return string
     */
    abstract protected function getApiUri(string $method): string;

    /**
     * @return string
     */
    abstract protected function getBaseAuthenticationUri(): string;

    /**
     * @return string
     */
    abstract protected function getTokenUri(): string;
}
