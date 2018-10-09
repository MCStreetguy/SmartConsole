<h1>SmartConsole - The smarter php console toolkit</h1>

[![GitHub issues](https://img.shields.io/github/issues/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole/issues)
[![GitHub forks](https://img.shields.io/github/forks/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole/network)
[![GitHub stars](https://img.shields.io/github/stars/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole/stargazers)
[![GitHub license](https://img.shields.io/github/license/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole/blob/master/LICENSE)
<!-- [![GitHub (pre-)release](https://img.shields.io/github/release/MCStreetguy/SmartConsole/all.svg)](https://github.com/MCStreetguy/SmartConsole)
[![GitHub (Pre-)Release Date](https://img.shields.io/github/release-date-pre/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole) -->
[![GitHub last commit](https://img.shields.io/github/last-commit/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole)
<!-- [![GitHub top language](https://img.shields.io/github/languages/top/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole)
[![GitHub contributors](https://img.shields.io/github/contributors/MCStreetguy/SmartConsole.svg)](https://github.com/MCStreetguy/SmartConsole) -->
[![Twitter](https://img.shields.io/twitter/url/https/github.com/MCStreetguy/SmartConsole.svg?style=social)](https://twitter.com/intent/tweet?text=Wow:&url=https%3A%2F%2Fgithub.com%2FMCStreetguy%2FSmartConsole)
<!-- [![Documentation Status](https://readthedocs.org/projects/smartconsole/badge/?version=latest)](https://smartconsole.readthedocs.io/en/latest/?badge=latest) -->

Have you ever wondered why it is so complicated to write a console application in PHP?
Whichever library you use, you'll end up with hundreds of lines of configuration before you can even read the first 'Hello World' in the terminal.

_**That's over now!**_ SmartConsole is the first console toolkit for PHP that you hardly have to configure at all.
All you do is write classes as you are used to and document them properly.
Smart Console analyzes your command handlers and makes all settings automatically so you can sit back and concentrate on your real goal.

SmartConsole is wrapped around the great [_webmozart/console_](https://github.com/webmozart/console) package and its approach is based on the CLI of the ingenious [_Neos CMS_](https://www.neos.io/).
Besides it merges together some of the basic functionalities from the underlying console-package and advanced features like progress-bars from [_CLImate_](https://climate.thephpleague.com/).  

By the way, SmartConsole even supports dependency injection for it's command handler classes through [_PHP-DI_](http://php-di.org/), which makes it very easy to use as a base for a standalone application. Read on to learn more about these and the remaining amazing features.