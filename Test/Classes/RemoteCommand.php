<?php

namespace MCStreetguy\SmartConsole\Test;

use MCStreetguy\SmartConsole\Annotations as CLI;
use MCStreetguy\SmartConsole\Command\AbstractCommand;

/**
 * Manage set of tracked repositories.
 */
class RemoteCommand extends AbstractCommand
{
    /**
     * Adds a remote named <name> for the repository at <url>.
     *
     * The command git fetch <name> can then be used to create and update remote-tracking branches <name>/<branch>.
     *
     * @param string $name The name of the remote to add.
     * @param string $url The url of the remote to add.
     * @param bool $fetch Run git fetch <name> immediately after the remote information is set up.
     * @CLI\ShortName(option="fetch",short="f")
     */
    public function addAction(string $name, string $url, bool $fetch = false)
    {
        // some logic
    }

    /**
     * Remove the remote named <name>.
     *
     * All remote-tracking branches and configuration settings for the remote are removed.
     *
     * @param string $name The name of the remote to remove.
     */
    public function removeAction(string $name)
    {
        // some logic
    }

    /**
     * Gives some information about the remote <name>.
     *
     * @param string $name The name of the remote to display.
     */
    public function showAction(string $name)
    {
        // some logic
    }

    /**
     * Show a list of existing remotes.
     *
     * @CLI\DefaultCommand
     */
    public function listAction()
    {
        // some logic
    }
}
