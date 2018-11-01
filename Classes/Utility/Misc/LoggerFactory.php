<?php

namespace MCStreetguy\SmartConsole\Utility\Misc;

use Webmozart\Assert\Assert;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Logger as Monologger;
use Monolog\Processor\PsrLogMessageProcessor;
use MCStreetguy\SmartConsole\Console;

/**
 * Factory class for the Monologger.
 */
class LoggerFactory
{
    /**
     * Cache for all previously build instances.
     * @var array
     */
    protected static $buildCache = [];

    /**
     * Build up the logger instance from the given path.
     *
     * @param string|null $path The logging directory
     * @param bool|null $rebuild Force a rebuild
     * @return Monologger
     * @throws \InvalidArgumentException
     */
    public static function build(string $path = null, bool $rebuild = false)
    {
        if ($path === null) {
            $path = Console::getLogDirPath();

            if ($path === null) {
                return null;
            }
        }

        if (array_key_exists($path, self::$buildCache) && $rebuild === false) {
            return self::$buildCache[$path];
        }

        Assert::readable($path, 'The given logdir path is not readable: %s!');
        Assert::writable($path, 'The given logdir path is not writable: %s!');
        Assert::directory($path, 'The given logdir path is no valid directory: %s!');

        $formatter = new LineFormatter('%datetime% > %level_name% > %message% %context% %extra%' . PHP_EOL);

        $defaultHandler = (new RotatingFileHandler(
            "$path/application.log",
            10,
            Monologger::DEBUG,
            false,
            0644,
            true
        ))->setFormatter($formatter);

        $errorHandler = (new RotatingFileHandler(
            "$path/error.log",
            15,
            Monologger::ERROR,
            false,
            0644,
            true
        ));

        $errorHandler->setFormatter($formatter)
            ->pushProcessor(new UidProcessor(16))
            ->pushProcessor(new ProcessIdProcessor)
            ->pushProcessor(new MemoryUsageProcessor(true, true))
            ->pushProcessor(new MemoryPeakUsageProcessor(true, true))
            ->pushProcessor(new IntrospectionProcessor(Monologger::ERROR));

        $obj = new Monologger('app', [
            $errorHandler,
            $defaultHandler,
            $debugHandler,
        ], [new PsrLogMessageProcessor]);

        self::$buildCache[$path] = $obj;

        return $obj;
    }
}
