<?php

namespace MCStreetguy\SmartConsole\Utility;

use League\CLImate\CLImate;
use Psr\Log\LoggerInterface;

class RawIO implements LoggerInterface
{
    /**
     * @var CLImate
     */
    protected $climate;

    public function __construct()
    {
        $this->climate = new CLImate;
    }

    // PSR-Logger Methods

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function emergency($message, array $context = [])
    {
        $this->climate->red()->bold()->out($this->interpolate($message, $context));
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function alert($message, array $context = [])
    {
        $this->climate->red()->bold()->out($this->interpolate($message, $context));
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function critical($message, array $context = [])
    {
        $this->climate->red()->out($this->interpolate($message, $context));
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function error($message, array $context = [])
    {
        $this->climate->red()->out($this->interpolate($message, $context));
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function warning($message, array $context = [])
    {
        $this->climate->yellow()->out($this->interpolate($message, $context));
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function notice($message, array $context = [])
    {
        $this->climate->cyan()->out($this->interpolate($message, $context));
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function info($message, array $context = [])
    {
        $this->climate->cyan()->out($this->interpolate($message, $context));
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function debug($message, array $context = [])
    {
        $this->climate->whisper($this->interpolate($message, $context));
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        $this->climate->out($this->interpolate($message, $context));
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string $message
     * @param array  $context
     *
     * @return string
     */
    public function interpolate(string $message, array $context = []) : string
    {
        $replacements = [];

        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replacements['{' . $key . '}'] = $val;
            }
        }

        return strtr($message, $replacements);
    }

    // Output

    public function success(string $message, array $context = [])
    {
        $this->climate->green()->out($this->interpolate($message, $context));
    }

    public function newline()
    {
        $this->climate->br();
    }

    public function clear()
    {
        $this->climate->clear();
    }
}
