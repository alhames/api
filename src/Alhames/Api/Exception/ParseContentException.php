<?php

/*
 * This file is part of the Common API Interface package.
 *
 * (c) Pavel Logachev <alhames@mail.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alhames\Api\Exception;

use Alhames\Api\HttpClient;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ParseContentException.
 */
class ParseContentException extends \RuntimeException implements ApiExceptionInterface
{
    /** @var ResponseInterface */
    private $response;
    /** @var string */
    private $format;

    /**
     * ParseContentException constructor.
     *
     * @param ResponseInterface $response
     * @param string            $format
     * @param \Throwable|null   $previous
     */
    public function __construct(ResponseInterface $response, string $format, \Throwable $previous = null)
    {
        $summary = RequestException::getResponseBodySummary($response);
        parent::__construct(sprintf('Expected %s format, got "%s": %s.', $format, HttpClient::getContentType($response), $summary), 0, $previous);
        $this->response = $response;
        $this->format = $format;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
