<?php

namespace MCStreetguy\SmartConsole\Utility;

use League\CLImate\CLImate;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class RawIO implements LoggerInterface
{
    /**
     * @var CLImate
     */
    protected $climate;

    /**
     * Constructs a new instance
     */
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
        $this->out($message, $context, 'red');
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
        $this->out($message, $context, 'red');
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
        $this->out($message, $context, 'yellow');
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
        $this->out($message, $context, 'cyan');
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
        $this->out($message, $context, 'cyan');
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
        $this->out($message, $context);
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

    /**
     * Output a message to the terminal.
     *
     * @param string $message The message to print
     * @param array|null $context Additional context variables that shall be interpolated into $message
     * @param string|null $color The foreground color of the message
     * @param string|null $background The background color of the message
     * @return void
     */
    public function out(string $message, array $context = [], string $color = null, string $background = null)
    {
        $this->climate($color, $background)->out(
            $this->interpolate($message, $context)
        );
    }

    /**
     * Output an inline message to the terminal.
     *
     * @param string $message The message to print
     * @param array|null $context Additional context variables that shall be interpolated into $message
     * @param string|null $color The foreground color of the message
     * @param string|null $background The background color of the message
     * @return void
     */
    public function inline(string $message, array $context = [], string $color = null, string $background = null)
    {
        $this->climate($color, $background)->inline(
            $this->interpolate($message, $context)
        );
    }

    /**
     * Print a success-message to the terminal.
     *
     * @param string $message The message to print
     * @param array|null $context Additional context variables that shall be interpolated into $message
     * @return void
     */
    public function success(string $message, array $context = [])
    {
        $this->out($message, $context, 'green');
    }

    /**
     * Print a linebreak to the terminal.
     *
     * @param int $count Optionally the amount of linebreaks that shall be printed. (Must be a value above 0)
     * @return void
     * @throws \InvalidArgumentException
     */
    public function newline(int $count = 1)
    {
        Assert::greaterThan($count, 0, "Invalid linebreak count given, expected '>0' recieved '%s'!");

        for ($i=0; $i < $count; $i++) {
            $this->climate->br();
        }
    }

    /**
     * Clear the terminal screen.
     *
     * @return void
     */
    public function clear()
    {
        $this->climate->clear();
    }

    // Helper methods

    /**
     * Prepare the CLImate instance with fore- and background colors.
     *
     * @param string|null $color The foreground color to set
     * @param string|null $background The background color to set
     * @return CLImate
     * @throws \InvalidArgumentException
     */
    protected function climate(string $color = null, string $background = null) : CLImate
    {
        $climate = $this->climate;

        if (!empty($color)) {
            self::validateColor($color);

            $climate = $climate->$color();
        }

        if (!empty($background)) {
            self::validateBackgroundColor($background);

            $climate = $climate->$background();
        }

        return $climate;
    }

    /**
     * Validates a foreground color string.
     *
     * @param string $color The color to validate
     * @return void
     * @throws \InvalidArgumentException
     */
    protected static function validateColor(string $color)
    {
        Assert::oneOf($color, [
            'black',
            'red',
            'green',
            'yellow',
            'blue',
            'magenta',
            'cyan',
            'lightGray',
            'darkGray',
            'lightRed',
            'lightGreen',
            'lightYellow',
            'lightBlue',
            'lightMagenta',
            'lightCyan',
            'white',
        ], "Invalid color: '%s'!");
    }

    /**
     * Validates a background color string.
     *
     * @param string $color The color to validate
     * @return void
     * @throws \InvalidArgumentException
     */
    protected static function validateBackgroundColor(string $backgroundColor)
    {
        Assert::startsWith($backgroundColor, 'background', "Invalid background color: '%s'!");

        $color = preg_replace_callback('/background(.)/', function ($matches) {
            return strtolower($matches[1]);
        }, $backgroundColor);

        static::validateColor($color);
    }
}
