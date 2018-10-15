<?php

namespace MCStreetguy\SmartConsole\Exceptions;

abstract class BaseException extends \RuntimeException
{
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return static::class . ": [{$this->code}]: {$this->message}\n";
    }

    public static function create(string $message = '', int $code = 0, \Exception $previous = null)
    {
        return new static($message, $code, $previous);
    }

    public static function throw(string $message = '', int $code = 0, \Exception $previous = null)
    {
        throw static::create($message, $code, $previous);
    }
}
