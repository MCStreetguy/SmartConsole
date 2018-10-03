<?php

namespace MCStreetguy\SmartConsole\Utility;

use Webmozart\Console\Api\IO\IO as IOApi;
use League\CLImate\CLImate;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;
use Webmozart\Console\UI\Component\Table;
use Webmozart\Console\UI\Style\TableStyle;
use League\CLImate\TerminalObject\Dynamic\Progress;

class IO implements LoggerInterface
{
    /**
     * @var IOApi
     */
    protected $io;

    /**
     * @var CLImate
     */
    protected $climate;

    /**
     * @var Progress
     */
    protected $currentProgress;

    /**
     * @var int
     */
    protected $currentProgressTotal;

    public function __construct(IOApi $io)
    {
        $this->io = $io;
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
    public function emergency(string $message, array $context = [])
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
    public function alert(string $message, array $context = [])
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
    public function critical(string $message, array $context = [])
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
    public function error(string $message, array $context = [])
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
    public function warning(string $message, array $context = [])
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
    public function notice(string $message, array $context = [])
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
    public function info(string $message, array $context = [])
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
    public function debug(string $message, array $context = [])
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

    // State-properties

    public function setInteractive(bool $interactive)
    {
        $this->io->setInteractive($interactive);
    }

    public function isInteractive() : bool
    {
        return $this->io->isInteractive();
    }

    public function setVerbosity(int $verbosity)
    {
        $this->io->setVerbosity($verbosity);
    }

    public function getVerbosity() : int
    {
        return $this->io->getVerbosity();
    }

    public function isVerbose() : bool
    {
        return $this->io->isVerbose();
    }

    public function isVeryVerbose() : bool
    {
        return $this->io->isVeryVerbose();
    }

    public function isDebug() : bool
    {
        return $this->io->isDebug();
    }

    public function isNormal() : bool
    {
        return $this->getVerbosity() === 0;
    }

    public function setQuiet(bool $quiet)
    {
        $this->io->setQuiet($quiet);
    }

    public function isQuiet() : bool
    {
        return $this->io->isQuiet();
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

    // Input

    public function prompt(string $question, string $default = null, bool $multiline = false, bool $hidden = false)
    {
        if ($hidden) {
            $input = $this->climate->password($question);
        } else {
            $input = $this->climate->input($question);
        }

        if ($default !== null) {
            $input->defaultTo($default);
        }

        if ($multiline) {
            $input->multiline();
        }

        return $input->prompt();
    }

    public function choose(string $question, array $answers, string $default = null, bool $strict = false)
    {
        $input = $this->climate->input($question);
        $input->accept($answers);

        if ($default !== null) {
            $input->defaultTo($default);
        }

        if ($strict) {
            $input->strict();
        }

        return $input->prompt();
    }

    public function confirm(string $question) : bool
    {
        $confirmation = $this->climate->confirm($question);
        return $confirmation->confirmed();
    }

    public function checkboxes(string $question, array $options)
    {
        $boxes = $this->climate->checkboxes($question, $options);
        return $boxes->prompt();
    }

    public function radiobuttons(string $question, array $options)
    {
        $radio = $this->climate->radio($question, $options);
        return $radio->prompt();
    }

    // Special Formatting

    public function table(array $data, bool $borderless = false)
    {
        Assert::allIsArray($data);

        if ($borderless) {
            $table = new Table(TableStyle::borderless());
        } else {
            $table = new Table(TableStyle::solidBorder());
        }

        if ($hasHeader = (array_keys($data[0]) !== range(0, count($data[0]) - 1))) {
            $table->setHeaderRow(array_keys($data[0]));
        }

        $table->setRows($data);

        $table->render($this->io);
    }

    public function columns(array $data, int $count = null)
    {
        if ($count !== null) {
            $this->climate->columns($data, $count);
        } else {
            $this->climate->columns($data);
        }
    }

    public function padding(array $data, int $size = null, string $character = null)
    {
        if ($size === null) {
            $longestKey = 0;

            foreach (array_keys($data) as $key) {
                if (($length = strlen($key)) > $longestKey) {
                    $longestKey = $length;
                }
            }

            $size = $longestKey + 5;
        }

        $padding = $this->climate->padding($size);

        if ($character !== null) {
            $character = substr($character, 0, 1);
            $padding->char($character);
        }

        foreach ($data as $key => $value) {
            $padding->label($key)->result($value);
        }
    }

    public function border(string $pattern = null, int $size = null)
    {
        $this->climate->border($pattern, $size);
    }

    // Progress

    public function startProgressBar(int $total = 100)
    {
        Assert::null($this->currentProgress, 'You cannot start multiple progress bars at once!');

        $this->currentProgressTotal = $total;
        $this->currentProgress = $this->climate->progress($total);
    }

    public function setProgress(int $progress, string $label = null)
    {
        Assert::notNull($this->currentProgress, 'You must start a progress bar before you can set the progress on it!');

        $this->currentProgress->current($progress, $label);
    }

    public function advanceProgress(int $step, string $label = null)
    {
        Assert::notNull($this->currentProgress, 'You must start a progress bar before you can advance the progress on it!');

        $this->currentProgress->advance($step, $label);
    }

    public function finishProgress(string $label = null)
    {
        $this->currentProgress->current($this->currentProgressTotal, $label);

        $this->currentProgress = null;
        $this->currentProgressTotal = null;
    }
}
