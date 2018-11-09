# SmartConsole

**The smarter php console toolkit.**

[![GitHub issues](https://img.shields.io/github/issues/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole/issues)
[![GitHub forks](https://img.shields.io/github/forks/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole/network)
[![GitHub stars](https://img.shields.io/github/stars/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole/stargazers)
[![GitHub license](https://img.shields.io/github/license/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole/blob/master/LICENSE)
[![GitHub (pre-)release](https://img.shields.io/github/release/MCStreetguy/SmartConsole/all.svg)](https://github.com/MCStreetguy/SmartConsole)
[![GitHub (Pre-)Release Date](https://img.shields.io/github/release-date-pre/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole)
[![GitHub last commit](https://img.shields.io/github/last-commit/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole)
[![GitHub top language](https://img.shields.io/github/languages/top/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole)
[![GitHub contributors](https://img.shields.io/github/contributors/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole)
[![Documentation Status](https://readthedocs.org/projects/smartconsole/badge/?version=latest)](https://smartconsole.readthedocs.io/en/latest/?badge=latest)

<!-- [![Twitter](https://img.shields.io/twitter/url/https/github.com/MCStreetguy/SmartConsole.svg?style=social)](https://twitter.com/intent/tweet?text=Wow:&url=https%3A%2F%2Fgithub.com%2FMCStreetguy%2FSmartConsole) -->

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

Check out the [official documentation](https://smartconsole.readthedocs.io/) for more information on how to use this library.

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
