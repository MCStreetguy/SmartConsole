<?php

namespace MCStreetguy\SmartConsole\Utility;

use Webmozart\Console\Api\IO\IO as IOApi;
use MCStreetguy\SmartConsole\Utility\Args as ArgsApi;
use Monolog\Logger as Monologger;
use Monolog\Handler\RotatingFileHandler;
use MCStreetguy\SmartConsole\Console;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\UidProcessor;
use Monolog\Formatter\LineFormatter;

/**
 * The extended logging-component, bundling the IO and Logfiles.
 */
class Logger extends IO
{
    /**
     * @var Monologger
     */
    protected $logger;

    /**
     * @inheritDoc
     */
    public function __construct(IOApi $io, ArgsApi $args)
    {
        parent::__construct($io, $args);

        $logPath = Console::getLogDirPath();
        
        if (!empty($logPath)) {
            $formatter = new LineFormatter('%datetime% > %level_name% > %message% %context% %extra%' . PHP_EOL);

            $debugHandler = new RotatingFileHandler("$logPath/debug.log", 5, Monologger::DEBUG, false, 0644, true);
            $debugHandler->setFormatter($formatter);

            $defaultHandler = new RotatingFileHandler("$logPath/application.log", 10, Monologger::INFO, true, 0644, true);
            $defaultHandler->setFormatter($formatter);

            $errorHandler = new RotatingFileHandler("$logPath/error.log", 15, Monologger::ERROR, false, 0644, true);
            $errorHandler->setFormatter($formatter);
            $errorHandler->pushProcessor(new MemoryUsageProcessor(true, true));
            $errorHandler->pushProcessor(new MemoryPeakUsageProcessor(true, true));
            $errorHandler->pushProcessor(new ProcessIdProcessor);
            $errorHandler->pushProcessor(new UidProcessor(16));
            $errorHandler->pushProcessor(new IntrospectionProcessor(Monologger::ERROR));

            $this->logger = new Monologger('app', [
                $errorHandler,
                $defaultHandler,
                $debugHandler,
            ], [new PsrLogMessageProcessor]);
        }
    }

    /**
     * @inheritDoc
     */
    public function debug($message, array $context = [])
    {
        $this->logDebug($message, $context);
        parent::debug($message, $context);
    }

    /**
     * Log a debug message directly to the application logfile.
     *
     * @param string $message The message to log
     * @param array|null $context Additional context variables
     * @return void
     */
    public function logDebug($message, array $context = [])
    {
        ($this->logger !== null) && $this->logger->debug($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function info($message, array $context = [])
    {
        $this->logInfo($message, $context);
        parent::info($message, $context);
    }

    /**
     * Log an info message directly to the application logfile.
     *
     * @param string $message The message to log
     * @param array|null $context Additional context variables
     * @return void
     */
    public function logInfo($message, array $context = [])
    {
        ($this->logger !== null) && $this->logger->info($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function notice($message, array $context = [])
    {
        $this->logNotice($message, $context);
        parent::notice($message, $context);
    }

    /**
     * Log a notice directly to the application logfile.
     *
     * @param string $message The message to log
     * @param array|null $context Additional context variables
     * @return void
     */
    public function logNotice($message, array $context = [])
    {
        ($this->logger !== null) && $this->logger->notice($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function warning($message, array $context = [])
    {
        $this->logWarning($message, $context);
        parent::warning($message, $context);
    }

    /**
     * Log a warning directly to the application logfile.
     *
     * @param string $message The message to log
     * @param array|null $context Additional context variables
     * @return void
     */
    public function logWarning($message, array $context = [])
    {
        ($this->logger !== null) && $this->logger->warning($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function error($message, array $context = [])
    {
        $this->logError($message, $context);
        parent::error($message, $context);
    }

    /**
     * Log an error directly to the application logfile.
     *
     * @param string $message The message to log
     * @param array|null $context Additional context variables
     * @return void
     */
    public function logError($message, array $context = [])
    {
        ($this->logger !== null) && $this->logger->error($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function critical($message, array $context = [])
    {
        $this->logCritical($message, $context);
        parent::critical($message, $context);
    }

    /**
     * Log a critical event directly to the application logfile.
     *
     * @param string $message The message to log
     * @param array|null $context Additional context variables
     * @return void
     */
    public function logCritical($message, array $context = [])
    {
        ($this->logger !== null) && $this->logger->critical($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function alert($message, array $context = [])
    {
        $this->logAlert($message, $context);
        parent::alert($message, $context);
    }

    /**
     * Log an alert directly to the application logfile.
     *
     * @param string $message The message to log
     * @param array|null $context Additional context variables
     * @return void
     */
    public function logAlert($message, array $context = [])
    {
        ($this->logger !== null) && $this->logger->alert($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function emergency($message, array $context = [])
    {
        $this->logEmergency($message, $context);
        parent::emergency($message, $context);
    }

    /**
     * Log an emergency event directly to the application logfile.
     *
     * @param string $message The message to log
     * @param array|null $context Additional context variables
     * @return void
     */
    public function logEmergency($message, array $context = [])
    {
        ($this->logger !== null) && $this->logger->emergency($message, $context);
    }
}
