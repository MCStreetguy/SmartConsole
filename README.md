# SmartConsole
**The smarter php console toolkit.**

[![GitHub issues](https://img.shields.io/github/issues/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole/issues)
[![GitHub forks](https://img.shields.io/github/forks/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole/network)
[![GitHub stars](https://img.shields.io/github/stars/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole/stargazers)
[![GitHub license](https://img.shields.io/github/license/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole/blob/master/LICENSE)
[![Twitter](https://img.shields.io/twitter/url/https/github.com/MCStreetguy/SmartConsole.svg?style=social)](https://twitter.com/intent/tweet?text=Wow:&url=https%3A%2F%2Fgithub.com%2FMCStreetguy%2FSmartConsole)

Have you ever wondered why it is so complicated to write a console application in PHP?
Whichever library you use, you'll end up with hundreds of lines of configuration before you can even read the first 'Hello World' in the terminal.

_That's over now!_ SmartConsole is the first console toolkit for PHP that you hardly have to configure at all.
All you do is write classes as you are used to and document them properly.
Smart Console analyzes your command handlers and makes all settings automatically so you can sit back and concentrate on your real goal.

SmartConsole is wrapped around the great [webmozart/console](https://github.com/webmozart/console) package and its approach is based on the CLI of the ingenious [Neos CMS](https://www.neos.io/).
Besides it merges together some of the basic functionalities from the underlying console-package and advanced features like progress-bars from [CLImate](https://climate.thephpleague.com/).

Read on to learn more about how to use it.

## Installation

Require the library through Composer:

``` bash
$ composer require mcstreetguy/smart-console
```

## Usage

In this section you find a little guide to your first CLI. If you search for the detailled reference, [have a look below](#reference).

### Basic Setup

To get started we first need to do some basic configuration (yes, we can't completely avoid this step) and tell our application what it's called and so on.
This is done by extending the present `Console`-class like this:

``` php
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

Just by looking at the code example, it should be clear what happens.
The `configure` method is called automatically on execution, so this is normally the place where we define our settings.

Since there are also `configure`-methods in the parent classes of our CLI you need to ensure that `parent::configure()` is called before you start with your own config!

To say a few words about the settings:
- `name` obviously defines you application name.
- `version` should also be self-explanatory.
- `displayName` is the beautified name of your application that gets display e.g. on the help page.
- `commands` is an array of command handler classes.

There are a [few more options](#reference) available but we'll get to this lateron. For now lets focus on the `HelloWorldCommand` we told our application to include.

### The binary

Before we can run our CLI to test it and see what happens, we need to create an executable that can be called from the terminal.
The reason why you still have to build the actual binary yourself is to keep SmartConsole as extensible as possible. A preshipped standard binary would prevent this.

``` php
#!/usr/bin/env php
<?php

use Vendor\MyCLI\MyApplication;

if (file_exists($autoload = __DIR__.'/../../../autoload.php')) {
    require_once $autoload;
} else {
    require_once __DIR__.'/../vendor/autoload.php';
}

MyApplication::run();

```

If you now execute this file in your terminal you should recieve a help-page, explaining the available commands and options.
That's all we have to do for our binary.

As an alternative to extending the `Console`-class and invoking that child-class you could also create a new instance and configure it from the binary directly as any of the config-methods is public. _Please note_ that this is considered bad practise.

However, for the sake of completeness: the following code would be the exact equivalent to the inheritance-version we [produced before](#basic-setup).

``` php
# ...
$cli = new Console;
$cli->init([
    'name' => 'my-application',
    'version' => '0.0.0-alpha',
    'displayName' => 'My Application',
    'commands' => [
        HelloWorldCommand::class
    ]
]);

$cli->execute();
```

### Our first command

With the above configuration our CLI would search for a `HelloWorldCommand` class to analyze it and add the respective configuration to the app on execution.
Obviously this would currently lead to a fatal error since the class does not exist yet, so let's create it:

``` php
namespace Vendor\MyCLI\Commands;

use MCStreetguy\SmartConsole\Command\AbstractCommand;

/**
 * Print 'Hello World!' to the terminal.
 */
class HelloWorldCommand extends AbstractCommand
{
    public function indexAction()
    {
        $this->io->log('Hello World!');
    }
}
```

If we now execute our binary it should show up 'hello-world' in the command list, with the description set as stated in the docblock of our class.

Wow, that was easy huh?
Like this you define all your commands, their options, arguments and help-texts. Even very complex commands do not require any more configuration than you've already written.

Below you find a detailled reference about all possibilites SmartConsole offers as well as an explanation to the analysis-algorithm if you want to dive deeper into the workings of this package.

## Reference

### General Configuration

_(to be written)_

### Commands

_(to be written)_

#### Arguments and Options

_(to be written)_

### The analysis-algorithm

_(to be written)_

### Best practises

_(to be written)_

## Contributing

If you find any bug or have a suggestion for an improvement or new feature, visit the [Issues-page](https://github.com/MCStreetguy/SmartConsole/issues) and leave a notice.
Please check if something similar has already been reported in any case to prevent duplicates.
Feel free to modify the source code on your own and create a [pull-request](https://github.com/MCStreetguy/SmartConsole/pulls) in conjunction with your improvement or bug.

## License

SmartConsole is licensed under the MIT license. A copy of that license is distributed together with the source code.
You may find that file under `/LICENSE` in the projects root directory or online at: https://github.com/MCStreetguy/SmartConsole/blob/master/LICENSE

### Disclaimer

_(taken from the LICENSE file, slightly adapted in favour of readability)_

> The software is provided "as is", without warranty of any kind, express or implied, including but not limited to the warranties of merchantability, fitness for a particular purpose and noninfringement. In no event shall the authors or copyright holders be liable for any claim, damages or other liability, wheter in an action of contract, tort or otherwise, arising from, out of or in connection with the software or the use or other dealings in the software.
