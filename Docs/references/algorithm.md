<h1>The analysis algorithm</h1>

Below you find a detailled explanation on how the analysis algorithm of SmartConsole works.
This is to prevent configuration errors of your commands since any information is retrieved from the class directly.

Any of the single parts throws an exception of type `MCStreetguy\SmartConsole\Exceptions\ConfigurationException` when anything goes wrong if not stated otherwise in the belonging section.

!!! tip "Developers Note"
    If you prefer to understand the procedure yourself, you are welcome to have a look at the [source code](https://github.com/MCStreetguy/SmartConsole/blob/master/Classes/Console.php#L157).

## Procedure

The main procedure is as follows:

1. The main name of the command is derived from the class name by removing the word `Command` from the end of the name and converting the string to `snake-case`
2. The main description is read from the class doc-block directly by joining the summary and the description string

### Methods

All public methods of the class are scanned and those collected that end with `Action`.
If only one action-method could be found, we have a so called 'simple command handler'.
In all other cases a sub-command is created for each of the methods and we got a 'complex command handler'.

1. The sub-command name is derived from the method name, as with the class name before
2. If it's not a simple command
    - the description for the sub-command is read from the method doc-block, as with the class doc-block before
    - If the `@CLI\DefaultCommand` or `@CLI\AnonymousCommand` annotations are present, the sub-command is marked as default and optionally anonymous. (See the [annotation reference](/reference/annotations) for more information)

### Parameters

All parameters of the method are scanned if there are any.
For each of them the following rules apply:

1. If we have an optional parameter an option is created respectively, an argument otherwise
2. The name is derived directly from the parameter name by converting it to `snake-case`
3. The description is read from the corresponding `@param`-tag in the method doc-block
4. If an option is created, the doc-block is additionally checked for the `@CLI\ShortName` annotation
5. The typehint of the parameter is used to determine the type
    - Supported types are currently: `bool`, `int`, `float` and `string`
    - If no typehint could be found but a default value is present, the type of the default value is used for comparison
6. If the parameter is variadic, the option/argument is marked as multi-valued

!!! tip "Developers Note"
    **Please note** that if you specify a boolean parameter with a default value currently, such a parameter will result in a no-value-option, meaning that you only have to specify the option or not to trigger it's state. Because of that structure, the default value will always be `false` (as the option is not present until the user specifies it explicitly).