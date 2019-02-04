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

use Alhames\Api\Exception\ParseContentException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class HttpClient.
 */
class HttpClient implements \Serializable, LoggerAwareInterface
{
    use LoggerAwareTrait;

    const USER_AGENT = 'alhames-api/'.ApiClientInterface::VERSION;

    /** @var float Query per second */
    private $qps;
    /** @var array */
    private $config;
    /** @var Client */
    private $client;
    /** @var float */
    private $lastRequestTime;

    /**
     * InternalApiClient constructor.
     *
     * @param int|float|null $qps
     * @param array          $config
     */
    public function __construct($qps = null, array $config = [])
    {
        $this->qps = $qps ? (float) $qps : null;
        $this->config = array_merge([
            RequestOptions::HTTP_ERRORS => true,
            RequestOptions::ALLOW_REDIRECTS => false,
            RequestOptions::HEADERS => [HttpInterface::HEADER_USER_AGENT => self::USER_AGENT],
        ], $config);
        $this->client = new Client($this->config);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return null|string
     */
    public static function getContentType(ResponseInterface $response): ?string
    {
        $contentTypes = $response->getHeader(HttpInterface::HEADER_CONTENT_TYPE);
        if (empty($contentTypes[0])) {
            return null;
        }

        if (false === strpos($contentTypes[0], ';')) {
            return $contentTypes[0];
        }

        return substr($contentTypes[0], 0, strpos($contentTypes[0], ';'));
    }

    /**
     * @param string     $method
     * @param string     $uri
     * @param array|null $get
     * @param array|null $post
     * @param array|null $files
     * @param array|null $headers
     *
     * @throws GuzzleException
     *
     * @return string
     */
    public function requestContent(string $method, string $uri, ?array $get = null, ?array $post = null, ?array $files = null, ?array $headers = null)
    {
        $options = $this->prepareOptions($get, $post, $files, $headers);

        return (string) $this->request($method, $uri, $options)->getBody();
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $options
     *
     * @throws GuzzleException
     *
     * @return ResponseInterface
     */
    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        if (null !== $this->qps && null !== $this->lastRequestTime) {
            $lastInterval = floor((microtime(true) - $this->lastRequestTime) * 1000000);
            $timeout = ceil(1000000 / $this->qps);
            if ($lastInterval < $timeout) {
                usleep($timeout - $lastInterval);
            }
        }

        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            throw $e;
        } finally {
            $this->lastRequestTime = microtime(true);
            if (null !== $this->logger) {
                $statusCode = !empty($response) ? $response->getStatusCode() : 0;
                $this->logger->debug(sprintf('"%s %s" %d', $method, $uri, $statusCode), [
                    'method' => $method,
                    'uri' => $uri,
                    'options' => $options,
                    'time' => $this->lastRequestTime,
                    'response' => $response ?? null,
                ]);
            }
        }

        return $response;
    }

    /**
     * @param string     $method
     * @param string     $uri
     * @param array|null $get
     * @param array|null $post
     * @param array|null $files
     * @param array|null $headers
     *
     * @throws GuzzleException
     * @throws ParseContentException
     *
     * @return mixed
     */
    public function requestJson(string $method, string $uri, ?array $get = null, ?array $post = null, ?array $files = null, ?array $headers = null)
    {
        $headers = array_merge([HttpInterface::HEADER_ACCEPT => 'application/json'], $headers ?: []);
        $options = $this->prepareOptions($get, $post, $files, $headers);
        $response = $this->request($method, $uri, $options);

        return $this->parseJsonResponse($response);
    }

    /**
     * @param string     $method
     * @param string     $uri
     * @param mixed      $data
     * @param array|null $headers
     *
     * @throws GuzzleException
     * @throws ParseContentException
     *
     * @return mixed
     */
    public function sendJson(string $method, string $uri, $data, ?array $headers = null)
    {
        $headers = array_merge([HttpInterface::HEADER_ACCEPT => 'application/json'], $headers ?: []);
        $options = $this->prepareOptions(null, null, null, $headers);
        $options[RequestOptions::JSON] = $data;
        $response = $this->request($method, $uri, $options);

        return $this->parseJsonResponse($response);
    }

    /**
     * @see http://openid.net/specs/openid-authentication-2_0.html#anchor4
     *
     * @param string     $method
     * @param string     $uri
     * @param array|null $get
     * @param array|null $post
     * @param array|null $files
     * @param array|null $headers
     *
     * @throws GuzzleException
     * @throws ParseContentException
     *
     * @return array
     */
    public function requestKeyValueForm(string $method, string $uri, ?array $get = null, ?array $post = null, ?array $files = null, ?array $headers = null): array
    {
        $options = $this->prepareOptions($get, $post, $files, $headers);
        $response = $this->request($method, $uri, $options);
        if (!preg_match_all("#(?<key>.+?)\:(?<value>.+)#", (string) $response->getBody(), $matches)) {
            throw new ParseContentException($response, 'key-value');
        }

        return array_combine($matches['key'], $matches['value']);
    }

    /**
     * @param ResponseInterface $response
     *
     * @throws ParseContentException
     *
     * @return mixed
     */
    public function parseJsonResponse(ResponseInterface $response)
    {
        if (!in_array(static::getContentType($response), ['application/json', 'application/javascript', 'text/javascript'], true)) {
            throw new ParseContentException($response, 'json');
        }

        $content = (string) $response->getBody();
        try {
            return \GuzzleHttp\json_decode($content);
        } catch (\InvalidArgumentException $e) {
            throw new ParseContentException($response, 'json', $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([$this->qps, $this->config, $this->lastRequestTime, $this->logger]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->qps, $this->config, $this->lastRequestTime, $this->logger) = unserialize($serialized);
        $this->client = new Client($this->config);
    }

    /**
     * @param null|string $option
     *
     * @return array|mixed|null
     */
    public function getConfig(?string $option = null)
    {
        return null === $option
            ? $this->config
            : (isset($this->config[$option]) ? $this->config[$option] : null);
    }

    /**
     * @todo Fix load from uri
     * @todo Fix load from string
     *
     * @param array|null $get
     * @param array|null $post
     * @param array|null $files
     * @param array|null $headers
     *
     * @return array
     */
    private function prepareOptions(?array $get = null, ?array $post = null, ?array $files = null, ?array $headers = null): array
    {
        $options = [
            RequestOptions::QUERY => $get ?: [],
            RequestOptions::HEADERS => $headers ?: [],
        ];
        if (!empty($files)) {
            $elements = [];
            foreach ($post ?: [] as $key => $value) {
                $elements[] = ['name' => $key, 'contents' => (string) $value];
            }
            foreach ($files as $key => $file) {
                if ($file instanceof \SplFileInfo) {
                    $fileName = $file->getFilename();
                    $contents = $file->isFile() ? fopen($file->getRealPath(), 'r') : file_get_contents($file->getPathname());
                } else {
                    $fileName = $key;
                    $contents = $file;
                }
                $elements[] = ['name' => $key, 'contents' => $contents, 'filename' => $fileName];
            }
            $options[RequestOptions::MULTIPART] = $elements;
        } elseif (!empty($post)) {
            $options[RequestOptions::FORM_PARAMS] = $post;
        }

        return $options;
    }
}
