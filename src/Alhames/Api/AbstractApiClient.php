<?php

namespace Alhames\Api;

use Psr\Log\LoggerInterface;

/**
 * Class AbstractApiClient.
 */
abstract class AbstractApiClient implements ApiClientInterface
{
    /** @var HttpClient */
    protected $httpClient;
    /** @var array */
    protected $options;

    /**
     * ApiClient constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->httpClient = new HttpClient($config['qps'] ?? null, $config['http_client'] ?? []);
        if (isset($config['logger']) && $config['logger'] instanceof LoggerInterface) {
            $this->httpClient->setLogger($config['logger']);
        }

        $this->options = $config['options'] ?? [];
    }

    /**
     * @return HttpClient
     */
    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }
}