<?php

namespace MCStreetguy\SmartConsole\Utility;

use DI\Container;
use Doctrine\Common\Annotations\AnnotationReader;
use MCStreetguy\SmartConsole\Annotations\Command\AnonymousCommand;
use MCStreetguy\SmartConsole\Annotations\Command\DefaultCommand;
use MCStreetguy\SmartConsole\Annotations\Command\ShortName;
use MCStreetguy\SmartConsole\Command\AbstractCommand;
use MCStreetguy\SmartConsole\Console;
use MCStreetguy\SmartConsole\Exceptions\ConfigurationException;
use MCStreetguy\SmartConsole\Utility\Helper\StringHelper;
use MCStreetguy\SmartConsole\Utility\Misc\HelpTextUtility;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use Webmozart\Assert\Assert;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Command\NoSuchCommandException;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Config\Config;
use MCStreetguy\SmartConsole\Annotations\Command\OptionalArgument;

class Analyzer
{
    /**
     * @var AnnotationReader
     */
    protected $annotationReader;

    /**
     * @var DocBlockFactory
     */
    protected $docBlockFactory;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Constructs a new instance.
     *
     * @param Container $container
     * @param AnnotationReader $annotationReader
     * @param DocBlockFactory $docBlockFactory
     */
    public function __construct(Container $container, AnnotationReader $annotationReader, DocBlockFactory $docBlockFactory)
    {
        $this->container = $container;
        $this->annotationReader = $annotationReader;
        $this->docBlockFactory = $docBlockFactory;
    }

    /**
     * Analyzes the specified command handler class and adds it's respective configuration to the given ApplicationConfig
     *
     * @param string $class The command handler to analyze
     * @param ApplicationConfig $config The configuration instance to add the command to
     */
    public function addCommand(string $class, ApplicationConfig &$config)
    {
        Assert::classExists($class, "The command handler class '$class' does not exist!");
        Assert::subclassOf($class, AbstractCommand::class, "The command handler class '$class' does not inherit from '" . AbstractCommand::class . "'!");

        $reflector = new \ReflectionClass($class);

        $className = $reflector->getShortName();

        Assert::endsWith($className, 'Command', "The command handler class '$class' has an invalid name: '%s'!");

        $commandName = str_replace('Command', '', $className);
        $commandName = StringHelper::camelToSnakeCase($commandName);

        Assert::false($this->hasCommand($commandName, $config), "A command with the name '$commandName' has already been declared!");

        $command = $config->beginCommand($commandName);

        $classDocBlock = $reflector->getDocComment();

        Assert::notEmpty($classDocBlock, "The command handler class '$class' is missing a descriptive docblock!");

        $classDocBlock = $this->docBlockFactory->create($classDocBlock);

        $summary = $classDocBlock->getSummary();

        Assert::notEmpty($summary, "The command handler doc-block of '$class' is missing a summary!");

        $command->setDescription($summary);

        if (!empty($description = (string) $classDocBlock->getDescription())) {
            // $description = HelpTextUtility::convertToHelpText($description);
            $command->setHelp($description);
        }

        $container = &$this->container;
        $command->setHandler(function () use ($class, $container) {
            return $container->get($class);
        });

        $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC | ~\ReflectionMethod::IS_STATIC);

        Assert::notEmpty($methods, "The command handler class '$class' defines no valid methods!");

        $actionMethods = array_filter($methods, function (\ReflectionMethod $elem) {
            return (bool) preg_match('/Action$/', $elem->getName());
        });

        Assert::notEmpty($actionMethods, "The command handler class '$class' defines no valid action methods!");

        if (count($actionMethods) === 1) {
            $method = array_shift($actionMethods);
            $cmdName = str_replace('Action', '', $method->getName());

            $command->setHandlerMethod("${cmdName}Cmd");

            $this->addArgsAndOptions($command, $method, $class);
        } else {
            foreach ($actionMethods as $method) {
                $cmdName = str_replace('Action', '', $method->getName());

                $subCommand = $command->beginSubCommand(StringHelper::camelToSnakeCase($cmdName));
                $subCommand->setHandlerMethod("${cmdName}Cmd");

                if ($this->annotationReader->getMethodAnnotation($method, DefaultCommand::class) !== null) {
                    $subCommand->markDefault();

                    if ($this->annotationReader->getMethodAnnotation($method, AnonymousCommand::class) !== null) {
                        $subCommand->markAnonymous();
                    }
                }

                $this->addArgsAndOptions($subCommand, $method, $class);

                $subCommand->end();
            }
        }

        $command->end();
    }

    /**
     * @internal
     * @param CommandConfig $config The config instance
     * @param \ReflectionMethod $method The method to analyze
     * @param string $class The class name that is currently beeing processed
     * @return void
     */
    protected function addArgsAndOptions(CommandConfig &$config, \ReflectionMethod $method, string $class)
    {
        $cmdName = str_replace('Action', '', $method->getName());

        $methodDocBlock = $method->getDocComment();
        Assert::notEmpty($methodDocBlock, "The action method '$cmdName' in class '$class' is missing a descriptive docblock!");
        $methodDocBlock = $this->docBlockFactory->create($methodDocBlock);

        $commandSummary = $methodDocBlock->getSummary();
        Assert::notEmpty($commandSummary, "The action method doc-block for '$cmdName' in '$class' is missing a summary!");
        $config->setDescription($commandSummary);

        $commandDescription = (string) $methodDocBlock->getDescription();

        if (!empty($commandDescription)) {
            $commandDescription = HelpTextUtility::convertToHelpText($commandDescription);
            $config->setHelp($commandDescription);
        }

        $params = $method->getParameters();

        foreach ($params as $parameter) {
            $name = $parameter->getName();
            $description = null;

            $paramTags = $methodDocBlock->getTagsByName('param');
            $paramTags = array_values(array_filter($paramTags, function (Param $elem) use ($name) {
                return ($elem->getVariableName() === $name);
            }));

            if (!empty($paramTags)) {
                $paramTag = $paramTags[0];
                $description = (string) $paramTag->getDescription();
            }

            $optionalArgumentMap = array_filter($this->annotationReader->getMethodAnnotations($method), function ($elem) use ($name) {
                return ($elem instanceof OptionalArgument) && ($elem->getArgument() === $name);
            });

            if ($parameter->isOptional() && empty($optionalArgumentMap)) {
                $defaultValue = $parameter->getDefaultValue();

                $optionName = StringHelper::camelToSnakeCase($name);

                Assert::false($this->hasOption($optionName, $config), "An option with the name '$optionName' has already been declared!");

                $shortNameMap = array_filter($this->annotationReader->getMethodAnnotations($method), function ($elem) use ($name) {
                    return ($elem instanceof ShortName) && ($elem->getOption() === $name);
                });

                if (!empty($shortNameMap)) {
                    $shortName = $shortNameMap[0]->getShort();
                    $flags = Option::PREFER_SHORT_NAME;
                } else {
                    $shortName = null;
                    $flags = Option::PREFER_LONG_NAME;
                }

                if ($parameter->hasType()) {
                    $type = $parameter->getType();
                    $type = $type->getName();
                } else {
                    $type = gettype($defaultValue);
                }

                switch ($type) {
                    case 'bool':
                        $flags = $flags | Option::BOOLEAN | Option::NO_VALUE;
                        $defaultValue = null;
                        break;
                    case 'boolean':
                        $flags = $flags | Option::BOOLEAN | Option::NO_VALUE;
                        $defaultValue = null;
                        break;
                    case 'int':
                        $flags = $flags | Option::INTEGER | Option::REQUIRED_VALUE;
                        break;
                    case 'integer':
                        $flags = $flags | Option::INTEGER | Option::REQUIRED_VALUE;
                        break;
                    case 'double':
                        $flags = $flags | Option::FLOAT | Option::REQUIRED_VALUE;
                        break;
                    case 'float':
                        $flags = $flags | Option::FLOAT | Option::REQUIRED_VALUE;
                        break;
                    case 'string':
                        $flags = $flags | Option::STRING | Option::REQUIRED_VALUE;
                        break;
                    default:
                        ConfigurationException::forInvalidOptionType($name, $cmdName);
                }

                if ($parameter->isVariadic()) {
                    $flags = $flags | Option::MULTI_VALUED;
                }

                $config->addOption($optionName, $shortName, $flags, $description, $defaultValue);
            } else {
                Assert::false($this->hasArgument($name, $config), "An argument with the name '$name' has already been declared!");

                $flags = !empty($optionalArgumentMap) ? Argument::OPTIONAL : Argument::REQUIRED;

                if ($parameter->hasType()) {
                    $type = $parameter->getType();

                    switch ((string) $type) {
                        case 'boolean':
                            $flags = $flags | Argument::BOOLEAN;
                            break;
                        case 'integer':
                            $flags = $flags | Argument::INTEGER;
                            break;
                        case 'double':
                            $flags = $flags | Argument::FLOAT;
                            break;
                        case 'float':
                            $flags = $flags | Argument::FLOAT;
                            break;
                        case 'string':
                            $flags = $flags | Argument::STRING;
                            break;
                        default:
                            throw new ConfigurationException(
                                "Argument '$name' in subcommand '$cmdName' has an invalid type!",
                                1538597361
                            );
                    }
                } else {
                    $flags = $flags | Argument::STRING;
                }

                if ($parameter->isVariadic()) {
                    $flags = $flags | Argument::MULTI_VALUED;
                }

                $config->addArgument($name, $flags, $description);
            }
        }
    }

    // Helper methods

    /**
     * Checks if the given configuration instance already has a command with the specified name.
     *
     * @param string $name The command name to check for
     * @param ApplicationConfig $config The configuration instance to search in
     * @return bool
     */
    protected function hasCommand(string $name, ApplicationConfig $config): bool
    {
        try {
            $config->getCommandConfig($name);
        } catch (NoSuchCommandException $e) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the given configuration instance already has an option with the specified name.
     *
     * @param string $name The short or long name of the option to check for
     * @param Config $config The configuration instance to search in
     * @return bool
     */
    protected function hasOption(string $name, Config $config): bool
    {
        foreach ($config->getOptions() as $option) {
            if ($option->getLongName() === $name || $option->getShortName() === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the given configuration instance already has an argument with the specified name.
     *
     * @param string $name The short or long name of the argument to check for
     * @param Config $config The configuration instance to search in
     * @return bool
     */
    protected function hasArgument(string $name, Config $config): bool
    {
        foreach ($config->getArguments() as $argument) {
            if ($argument->getName() === $name) {
                return true;
            }
        }

        return false;
    }
}
