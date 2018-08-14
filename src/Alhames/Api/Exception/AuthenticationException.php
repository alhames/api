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

    /**
     * @param string $argument
     * @param array  $data
     *
     * @return static
     */
    public static function invalidArgument(string $argument, array $data)
    {
        return new static($data, sprintf('Argument "%s" is empty or invalid.', $argument));
    }
}
