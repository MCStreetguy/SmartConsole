<?php

namespace MCStreetguy\SmartConsole\Utility;

use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Dynamic\Progress;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;
use Webmozart\Console\Api\IO\IO as IOApi;
use Webmozart\Console\UI\Component\Table;
use Webmozart\Console\UI\Style\TableStyle;

class IO extends RawIO
{
    /**
     * @var IOApi
     */
    protected $io;

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
        parent::__construct();

        $this->io = $io;
    }

    // State-properties

    public function setInteractive(bool $interactive)
    {
        $this->io->setInteractive($interactive);
    }

    public function isInteractive(): bool
    {
        return $this->io->isInteractive();
    }

    public function setVerbosity(int $verbosity)
    {
        $this->io->setVerbosity($verbosity);
    }

    public function getVerbosity(): int
    {
        return $this->io->getVerbosity();
    }

    public function isVerbose(): bool
    {
        return $this->io->isVerbose();
    }

    public function isVeryVerbose(): bool
    {
        return $this->io->isVeryVerbose();
    }

    public function isDebug(): bool
    {
        return $this->io->isDebug();
    }

    public function isNormal(): bool
    {
        return $this->getVerbosity() === 0;
    }

    public function setQuiet(bool $quiet)
    {
        $this->io->setQuiet($quiet);
    }

    public function isQuiet(): bool
    {
        return $this->io->isQuiet();
    }

    // Input

    public function prompt(
        string $question,
        bool $forceAnswer = false,
        string $default = null,
        bool $multiline = false,
        bool $hidden = false,
        string $color = null,
        string $background = null
    ) {
        $climate = $this->climate($color, $background);

        if ($hidden) {
            $input = $climate->password($question);
        } else {
            $input = $climate->input($question);
        }

        if ($default !== null) {
            $input->defaultTo($default);
        }

        if ($multiline) {
            $input->multiline();
        }

        $result = $input->prompt();

        if ($forceAnswer) {
            while (empty($result)) {
                $result = $input->prompt();
            }
        }

        return $result;
    }

    public function choose(
        string $question,
        array $answers,
        string $default = null,
        bool $hint = false,
        bool $strict = false,
        string $color = null,
        string $background = null
    ) {
        $input = $this->climate($color, $background)->input($question);
        $input->accept($answers, $hint);

        if ($default !== null) {
            $input->defaultTo($default);
        }

        if ($strict) {
            $input->strict();
        }

        return $input->prompt();
    }

    public function confirm(string $question, string $color = 'yellow', string $background = null): bool
    {
        $confirmation = $this->climate($color, $background)->confirm($question);
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

    public function simpleTable(array $data, bool $borderless = false)
    {
        if ($borderless) {
            $list = new Table(TableStyle::borderless());
        } else {
            $list = new Table(TableStyle::solidBorder());
        }

        foreach ($data as $key => $value) {
            $list->addRow([$key, $value]);
        }

        $list->render($this->io);
    }

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

    public function columns(array $data, int $count = null, string $color = null, string $background = null)
    {
        $climate = $this->climate($color, $background);

        if ($count !== null) {
            $climate->columns($data, $count);
        } else {
            $climate->columns($data);
        }
    }

    public function padding(
        array $data,
        int $size = null,
        string $character = null,
        string $color = null,
        string $background = null
    ) {
        if ($size === null) {
            $longestKey = 0;

            foreach (array_keys($data) as $key) {
                if (($length = strlen($key)) > $longestKey) {
                    $longestKey = $length;
                }
            }

            $size = $longestKey + 5;
        }

        $padding = $this->climate($color, $background)->padding($size);

        if ($character !== null) {
            $character = substr($character, 0, 1);
            $padding->char($character);
        }

        foreach ($data as $key => $value) {
            $padding->label($key)->result($value);
        }
    }

    public function border(string $pattern = null, int $size = null, string $color = null, string $background = null)
    {
        $this->climate($color, $background)->border($pattern, $size);
    }

    // Progress

    public function startProgressBar(int $total = 100, string $color = null, string $background = null)
    {
        Assert::null($this->currentProgress, 'You cannot start multiple progress bars at once!');

        $this->currentProgressTotal = $total;
        $this->currentProgress = $this->climate($color, $background)->progress($total);
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

    // Helper methods

    protected function climate(string $color = null, string $background = null): CLImate
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

    protected static function validateBackgroundColor(string $backgroundColor)
    {
        Assert::startsWith($backgroundColor, 'background', "Invalid background color: '%s'!");

        $color = preg_replace_callback('/background(.)/', function ($matches) {
            return strtolower($matches[1]);
        }, $backgroundColor);

        static::validateColor($color);
    }
}
