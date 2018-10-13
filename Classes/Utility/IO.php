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

    /**
     * Constructs a new instance.
     *
     * @param IOApi $io
     */
    public function __construct(IOApi $io)
    {
        parent::__construct();

        $this->io = $io;
    }

    // State-properties

    /**
     * Set the interactive state of the IO.
     *
     * @param bool $interactive
     * @return void
     */
    public function setInteractive(bool $interactive)
    {
        $this->io->setInteractive($interactive);
    }

    /**
     * Get if the IO is in interactive mode.
     *
     * @return bool
     */
    public function isInteractive(): bool
    {
        return $this->io->isInteractive();
    }

    /**
     * Set the verbosity of the IO.
     *
     * @param int $verbosity
     * @return void
     */
    public function setVerbosity(int $verbosity)
    {
        $this->io->setVerbosity($verbosity);
    }

    /**
     * Get the verbosity of the IO.
     *
     * @return int
     */
    public function getVerbosity(): int
    {
        return $this->io->getVerbosity();
    }

    /**
     * Get if the IO is in verbose mode (the application has been run with '-v').
     *
     * @return bool
     */
    public function isVerbose(): bool
    {
        return $this->io->isVerbose();
    }

    /**
     * Get if the IO is in very-verbose mode (the application has been run with '-vv').
     *
     * @return bool
     */
    public function isVeryVerbose(): bool
    {
        return $this->io->isVeryVerbose();
    }

    /**
     * Get if the IO is in debug mode (the application has been run with '-vvv').
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->io->isDebug();
    }

    /**
     * Get if the IO is in normal mode (the application has not been run with any '-v*' flag).
     *
     * @return bool
     */
    public function isNormal(): bool
    {
        return $this->getVerbosity() === 0;
    }

    /**
     * Set the state of IO quiet mode.
     *
     * @param bool $quiet
     * @return void
     */
    public function setQuiet(bool $quiet)
    {
        $this->io->setQuiet($quiet);
    }

    /**
     * Get if the IO is in quiet mode.
     *
     * @return bool
     */
    public function isQuiet(): bool
    {
        return $this->io->isQuiet();
    }

    // Input

    /**
     * Prompt the user for input.
     *
     * @param string $question The message to display to the user before prompting
     * @param bool $forceAnswer Force the user to input something, if the result is empty prompt again
     * @param string $default The default value of the prompt if the user omits entering anything
     * @param bool $multiline Make the input span over multiple lines and wait for ^D before returning
     * @param bool $hidden Make the user input hidden (i.e. for a password prompt)
     * @param string $color The foreground color to use
     * @param string $background The background color to use
     * @return string
     * @throws \InvalidArgumentException
     */
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

    /**
     * Prompt the user for input, comparing it against a set of valid answers.
     *
     * @param string $question The message to display to the user before prompting
     * @param array $answers The possible answer the user may give
     * @param string $default The default value of the prompt if the user omits entering anything
     * @param bool $hint Give the user a hint of all possibilities
     * @param bool $strict Make the comparison case-sensitive
     * @param string $color The foreground color to use
     * @param string $background The background color to use
     * @return string
     * @throws \InvalidArgumentException
     */
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

    /**
     * Ask the user for confirmation.
     *
     * @param string $question The message to display to the user before prompting
     * @param string $color The foreground color to use
     * @param string $background The background color to use
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function confirm(string $question, string $color = 'yellow', string $background = null): bool
    {
        $confirmation = $this->climate($color, $background)->confirm($question);
        return $confirmation->confirmed();
    }

    /**
     * Let the user select several answers from a checkbox-UI.
     *
     * NOTE: The checkbox-UI only works in non-Windows environments as of right now.
     *
     * @param string $question The message to display to the user before prompting
     * @param array $options The possible answer the user may choose between
     * @return string
     */
    public function checkboxes(string $question, array $options)
    {
        $boxes = $this->climate->checkboxes($question, $options);
        return $boxes->prompt();
    }

    /**
     * Let the user select one answer from a radiobutton-UI.
     *
     * NOTE: The radiobutton-UI only works in non-Windows environments as of right now.
     *
     * @param string $question The message to display to the user before prompting
     * @param array $options The possible answer the user may choose between
     * @return string
     */
    public function radiobuttons(string $question, array $options)
    {
        $radio = $this->climate->radio($question, $options);
        return $radio->prompt();
    }

    // Special Formatting

    /**
     * Print a simple table to the terminal.
     *
     * @param array $data An associative array of data mappings
     * @param bool $borderless Make the table borderless
     * @return void
     */
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

    /**
     * Print a complex table to the terminal.
     *
     * @param array $data An array of associative arrays of data mappings (the keys from the first sub-array are used as header row)
     * @param bool $borderless Make the table borderless
     * @return void
     * @throws \InvalidArgumentException
     */
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

    /**
     * Print data in columns to the terminal (e.g. like 'ls' does).
     *
     * @param array $data An array of data to print
     * @param int $count The amount of columns to be printed (this is recognized automatically if unset)
     * @param string $color The foreground color to use
     * @param string $background The background color to use
     * @return void
     * @throws \InvalidArgumentException
     */
    public function columns(array $data, int $count = null, string $color = null, string $background = null)
    {
        $climate = $this->climate($color, $background);

        if ($count !== null) {
            $climate->columns($data, $count);
        } else {
            $climate->columns($data);
        }
    }

    /**
     * Print padded data to the terminal.
     *
     * @param array $data An associative array of data mappings
     * @param int $size The space between the keys and values (this is recognized automatically if unset)
     * @param string $character The character symbol to use as spacing
     * @param string $color The foreground color to use
     * @param string $background The background color to use
     * @return void
     * @throws \InvalidArgumentException
     */
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

    /**
     * Print a border-line to the terminal.
     *
     * @param string $pattern The pattern to print
     * @param int $size The desired width of the line (defaults to the width of the terminal)
     * @param string $color The foreground color to use
     * @param string $background The background color to use
     * @return void
     * @throws \InvalidArgumentException
     */
    public function border(string $pattern = null, int $size = null, string $color = null, string $background = null)
    {
        $this->climate($color, $background)->border($pattern, $size);
    }

    // Progress

    /**
     * Start a progress bar in the terminal screen.
     *
     * @param int $total The maximum steps of the progress bar
     * @param string $color The foreground color to use
     * @param string $background The background color to use
     * @return void
     * @throws \InvalidArgumentException
     */
    public function startProgressBar(int $total = 100, string $color = null, string $background = null)
    {
        Assert::null($this->currentProgress, 'You cannot start multiple progress bars at once!');

        $this->currentProgressTotal = $total;
        $this->currentProgress = $this->climate($color, $background)->progress($total);
    }

    /**
     * Set the progression on the current progress bar.
     *
     * NOTE: This requires to start a progress bar first!
     *
     * @param int $progress The current progress to set
     * @param string $label An optional label, describing the current step
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setProgress(int $progress, string $label = null)
    {
        Assert::notNull($this->currentProgress, 'You must start a progress bar before you can set the progress on it!');

        $this->currentProgress->current($progress, $label);
    }

    /**
     * Advance the progression on the current progress bar.
     *
     * NOTE: This requires to start a progress bar first!
     *
     * @param int $step The number of steps to advance the progress by
     * @param string $label An optional label, describing the current step
     * @return void
     * @throws \InvalidArgumentException
     */
    public function advanceProgress(int $step, string $label = null)
    {
        Assert::notNull($this->currentProgress, 'You must start a progress bar before you can advance the progress on it!');

        $this->currentProgress->advance($step, $label);
    }

    /**
     * Finish the current progress bar.
     *
     * NOTE: This requires to start a progress bar first!
     *
     * @param string An optional label, describing the finalization
     * @return void
     * @throws \InvalidArgumentException
     */
    public function finishProgress(string $label = null)
    {
        $this->currentProgress->current($this->currentProgressTotal, $label);

        $this->currentProgress = null;
        $this->currentProgressTotal = null;
    }
}
