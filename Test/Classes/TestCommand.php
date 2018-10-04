<?php

namespace MCStreetguy\SmartConsole\Test;

use MCStreetguy\SmartConsole\Command\AbstractCommand;

/**
 * Test for general functionality.
 */
class TestCommand extends AbstractCommand
{
    /**
     * Print 'Hello World!' to the terminal.
     */
    public function demoAction()
    {
        $this->io->success('Hello World!');
    }

    /**
     * List some demo stuff.
     *
     * @param string $filter Apply the given filter to the listing.
     * @param bool $someOption Trigger some alternate action.
     */
    public function listAction(string $filter, bool $someOption = false)
    {
        if ($someOption === true) {
            $this->io->warning($filter);
        } else {
            $this->io->notice($filter);
        }
    }
}
