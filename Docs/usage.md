<h1>Usage Guide</h1>

This page covers the basic usage of this toolkit, just like a step-by-step guide.
If you've never used SmartConsole before, you've come to the right place.
If you are looking for a fully detailled listing of features instead, head on to the [References](/references).

## Installation

Require the toolkit via composer:

```bash
$ composer require mcstreetguy/smart-console
```

## Basic Setup

To get started with SmartConsole we need to do some basic configuration (_yes, we can't completely avoid this step_) and tell our application what it's called, which version it's on and - especially - what commands are available.
There are several ways to achieve this but for now we'll just stick to the best practices to keep it short and simple.

!!! tip "Developers Note"
    In exceptional cases it may happen that this approach does not fit your project setup. In this case you should of course choose a different solution. On the examples page you will find several alternatives but it is strongly discouraged to use them prematurely.

### App Configuration

To do so, we simply create a class that inherits from the base class `MCStreetguy\SmartConsole\Console`:

```php
<?php
namespace Vendor\MyCLI;

use MCStreetguy\SmartConsole\Console;

class MyApplication extends Console
{
    protected function configure()
    {
        parent::configure();

        $this->init([
            'name' => 'my-application',
            'version' => '0.0.0-alpha',
            'displayName' => 'My Application'
        ]);
    }
}
```

Just by looking at the code example, it should be clear what happens. The configure method is called automatically on instantiation, so this is normally the place where we define our settings.

!!! info "Please note..."
    Since there are also configure-methods in the parent classes of our CLI that are required for the application to work properly you need to ensure that `parent::configure()` is called before you start with your own config!

To say a few more words about the above example:

- `name` is obviously the actual name of our application. Typically this is the same as the binary file name we later want to execute.  
- `version` defines the version of our application. This can be any semantic version string.  
- `displayName` is the readable form of the `name` property. This is used e.g. on the help page.  

There are a few more options available but for now this suffice.
We got barely 20 lines of code and that's all we have to do with configuration.
In fact, we'll have to come back to this file to define our commands, but we'll deal with that later.

### The executable

Before we get to the really exciting part, we should create a binary.
Like the other components of SmartConsole, this isn't much work and has the advantage that we can test the commands we are about to create directly.

``` php
#!/usr/bin/env php
<?php

if (file_exists($autoload = __DIR__.'/../../../autoload.php')) {
    require_once $autoload;
} else {
    require_once __DIR__.'/../vendor/autoload.php';
}

Vendor\MyCLI\MyApplication::run();
```

The complex if-condition at the beginning of our code loads the composer autoload file.
It assumes that you place your binary in a subfolder of your project root (such as `bin/`).
The else branch is used to require the autoloader if the package got installed in another project below the `vendor/` folder.

The last line then executes our previously configured application.
You can verify that everything works as expected by executing the binary.
It should show up a help page describing all possible commands and options (the default ones currently):

    My Application version 0.0.0-alpha

    USAGE
    my-application [-h] [-q] [-v [<level>]] [-V] [--ansi] [--no-ansi] [-n] <command> [<arg1>] ... [<argN>]

    ARGUMENTS
    <command>              The command to execute
    <arg>                  The arguments of the command

    GLOBAL OPTIONS
    -h (--help)            Display help about the command
    -q (--quiet)           Do not output any message
    -v (--verbose)         Increase the verbosity of messages: "-v" for normal output, "-vv" for more verbose output and "-vvv" for debug
    -V (--version)         Display this application version
    --ansi                 Force ANSI output
    --no-ansi              Disable ANSI output
    -n (--no-interaction)  Do not ask any interactive question

    AVAILABLE COMMANDS
    help                   Display the manual of a command

As you can see it is almost too easy to write a console application with SmartConsole.
Even though the CLI can't do anything meaningful currently, at least it's working already.

### Our first command

Now that we have everything up and running we can start adding commands to our application.
This is done by creating command handler classes and telling the CLI about these.

So, one by one:

``` php
<?php
namespace Vendor\MyCLI\Command;

use MCStreetguy\SmartConsole\Command\AbstractCommand;

/**
 * Print 'Hello World!' to the terminal.
 */
class HelloWorldCommand extends AbstractCommand
{
    public function indexAction()
    {
        $this->io->out('Hello World!');
    }
}
```

We just created a command named 'hello-world' that prints out 'Hello World!' on execution.  
_(Yeah, I know, not very inventive, but it's enough for a demonstration)_

You have to follow some guidelines so that SmartConsole recognizes everything correctly.
For example, the class name of a command handler must end in `Command` and all relevant methods must end in `Action`.
Relevant in this context means methods that represent either the sole or different functions of the command.
I'll explain the [exact procedure of the algorithm](#the-analysis-algorithm) later.

Now we extend our app config to tell it about our new command:

``` php hl_lines="17 18 19"
<?php
namespace Vendor\MyCLI;

use MCStreetguy\SmartConsole\Console;
use Vendor\MyCLI\Commands\HelloWorldCommand;

class MyApplication extends Console
{
    protected function configure()
    {
        parent::configure();

        $this->init([
            'name' => 'my-application',
            'version' => '0.0.0-alpha',
            'displayName' => 'My Application',
            'commands' => [
                HelloWorldCommand::class
            ]
        ]);
    }
}
```

Let's have a look at the help page again to check for the freshly created command:

``` text hl_lines="21"
$ ./bin/my-application help
My Application version 0.0.0-alpha

USAGE
  my-application [-h] [-q] [-v [<level>]] [-V] [--ansi] [--no-ansi] [-n] <command> [<arg1>] ... [<argN>]

ARGUMENTS
  <command>              The command to execute
  <arg>                  The arguments of the command

GLOBAL OPTIONS
  -h (--help)            Display help about the command
  -q (--quiet)           Do not output any message
  -v (--verbose)         Increase the verbosity of messages: "-v" for normal output, "-vv" for more verbose output and "-vvv" for debug
  -V (--version)         Display this application version
  --ansi                 Force ANSI output
  --no-ansi              Disable ANSI output
  -n (--no-interaction)  Do not ask any interactive question

AVAILABLE COMMANDS
  hello-world            Print 'Hello World!' to the terminal.
  help                   Display the manual of a command

```

``` text
$ ./bin/my-application help hello-world
USAGE
  my-application hello-world

GLOBAL OPTIONS
  -h (--help)            Display help about the command
  -q (--quiet)           Do not output any message
  -v (--verbose)         Increase the verbosity of messages: "-v" for normal output, "-vv" for more verbose output and "-vvv" for debug
  -V (--version)         Display this application version
  --ansi                 Force ANSI output
  --no-ansi              Disable ANSI output
  -n (--no-interaction)  Do not ask any interactive question

```

Wow, that was easy huh? Like this you define all your commands, their options, arguments and help-texts. Even very complex commands do not require any more configuration than you've already written.
Speaking of which, let's implement a more complicated command right now.

### A more complex command

Whereas 'complex' means that our command should have several sub-commands available.
Imagine this as with the Git CLI, where we sometimes have to type several command names one after the other.
For example the `git remote` command has the sub-commands `add`, `remove`, `show` and so on.

Let us take this example seriously and put it into practice.
As you can imagine our command handler now looks a lot more complex.
Every action method must now have a doc block and [annotations](https://www.doctrine-project.org/projects/doctrine-annotations/en/latest/index.html#introduction) have been added.
Let's take a look at the code first and then at the help page to see what happens before I explain exactly what we do.

``` php
<?php
namespace Vendor\MyCLI\Command;

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
```

``` text hl_lines="23"
$ ./bin/my-application help
My Application version 0.0.0-alpha

USAGE
  my-application [-h] [-q] [-v [<level>]] [-V] [--ansi] [--no-ansi] [-n] <command> [<arg1>] ... [<argN>]

ARGUMENTS
  <command>              The command to execute
  <arg>                  The arguments of the command

GLOBAL OPTIONS
  -h (--help)            Display help about the command
  -q (--quiet)           Do not output any message
  -v (--verbose)         Increase the verbosity of messages: "-v" for normal output, "-vv" for more verbose output and "-vvv" for debug
  -V (--version)         Display this application version
  --ansi                 Force ANSI output
  --no-ansi              Disable ANSI output
  -n (--no-interaction)  Do not ask any interactive question

AVAILABLE COMMANDS
  hello-world            Print 'Hello World!' to the terminal.
  help                   Display the manual of a command
  remote                 Manage set of tracked repositories.

```

``` text
$ ./bin/my-application help remote
USAGE
      my-application remote
  or: my-application remote add [-f] <name> <url>
  or: my-application remote remove <name>
  or: my-application remote show <name>
  or: my-application remote list

COMMANDS
  add
    Adds a remote named <name> for the repository at <url>.
    The command git fetch <name> can then be used to create and update remote-tracking branches <name>/<branch>.

    <name>               The name of the remote to add.
    <url>                The url of the remote to add.

    -f (--fetch)         Run git fetch <name> immediately after the remote information is set up.

  list
    Show a list of existing remotes.

  remove
    Remove the remote named <name>.
    All remote-tracking branches and configuration settings for the remote are removed.

    <name>               The name of the remote to remove.

  show
    Gives some information about the remote <name>.

    <name>               The name of the remote to display.

GLOBAL OPTIONS
  -h (--help)            Display help about the command
  -q (--quiet)           Do not output any message
  -v (--verbose)         Increase the verbosity of messages: "-v" for normal output, "-vv" for more verbose output and "-vvv" for debug
  -V (--version)         Display this application version
  --ansi                 Force ANSI output
  --no-ansi              Disable ANSI output
  -n (--no-interaction)  Do not ask any interactive question

```

As you can see, SmartConsole reads all of our doc-blocks and creates the respective sub-commands.
The parameters of our methods are also analysed and turned into arguments for required ones and into options for optional params.
In addition we define a short name for our `fetch` option as well as a default command, which means the sub-command beeing executed when no sub-command name is given.

!!! tip "Developers Note"
    This annotation is actually required for a complex command to work properly.
    Leaving it out or specifying it twice or even more often leads to exceptions!