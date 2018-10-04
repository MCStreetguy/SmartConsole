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

``` bash hl_lines="21"
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

