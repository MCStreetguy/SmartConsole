<h1>The analysis algorithm</h1>

Below you find a detailled explanation on how the analysis algorithm of SmartConsole works.
This is to prevent configuration errors of your commands since any information is retrieved from the class directly.

Any of the single parts throws an exception of type `MCStreetguy\SmartConsole\Exceptions\ConfigurationException` when anything goes wrong if not stated otherwise in the belonging section.

## Main command name

The main name of the command is derived directly from the class name by removing the word `Command` from the end of the name and converting the string to `snake-case`.

## Main description

The description of the command is read from the class doc-block by joining the summary and the description if available from it.

## Subcommands

By scanning any of the handler class methods that end with `Action` and are public the sub-commands are derived.  
If only one action-method is present no sub-commands are configured, instead that method gets set as main method for the command handler that is beeing invoked on execution.  
If more than one action method is available, a sub-command is created for each of them unless the `@CLI\AnonymousCommand` annotation is present. The name is resolved in the same way as the [main command name](#main-command-name).

### Description

The sub-command description is read from the method doc-block.

### Arguments

...

### Options

...