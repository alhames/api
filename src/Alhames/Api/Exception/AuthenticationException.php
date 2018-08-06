<?php

namespace Alhames\Api\Exception;

/**
 * Class AuthenticationException.
 */
class AuthenticationException extends \RuntimeException implements ApiExceptionInterface
{
    /** @var array */
    private $data;

    /**
     * AuthenticationException constructor.
     *
     * @param array           $data
     * @param string          $message
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct(array $data = [], string $message = '', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
