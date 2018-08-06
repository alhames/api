<?php

namespace Alhames\Api\Exception;

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
        $body = $response->getBody();
        $content = $body->read(100).($body->eof() ? '' : '...');
        parent::__construct(sprintf('Expected %s format, got "%s".', $format, $content), 0, $previous);
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
