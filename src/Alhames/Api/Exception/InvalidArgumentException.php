<?php

namespace Alhames\Api\Exception;

/**
 * Class InvalidArgumentException.
 */
class InvalidArgumentException extends \InvalidArgumentException implements ApiExceptionInterface
{
    /** @var string */
    private $argument;

    /**
     * InvalidArgumentException constructor.
     *
     * @param string          $argument
     * @param string          $message
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $argument, string $message = '', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ?: sprintf('Argument "%s" is not valid.', $argument), $code, $previous);
        $this->argument = $argument;
    }

    /**
     * @return string
     */
    public function getArgument(): string
    {
        return $this->argument;
    }
}
