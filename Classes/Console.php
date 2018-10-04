<?php

namespace MCStreetguy\SmartConsole;

use Doctrine\Common\Annotations\AnnotationReader;
use MCStreetguy\SmartConsole\Annotations\AnonymousCommand;
use MCStreetguy\SmartConsole\Annotations\DefaultCommand;
use MCStreetguy\SmartConsole\Annotations\OptionNameMap;
use MCStreetguy\SmartConsole\Annotations\ShortName;
use MCStreetguy\SmartConsole\Command\AbstractCommand;
use MCStreetguy\SmartConsole\Exceptions\ConfigurationException;
use MCStreetguy\SmartConsole\Utility\RawIO;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlockFactory;
use Webmozart\Assert\Assert;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Config\DefaultApplicationConfig as ApplicationConfig;
use Webmozart\Console\ConsoleApplication;

class Console extends ApplicationConfig
{
    public static function run()
    {
        $config = new static;
        $config->setTerminateAfterRun(false);
        $config->setCatchExceptions(false);

        $cli = new ConsoleApplication(new static);

        try {
            $result = $cli->run();
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = $e->getCode();

            $rawIO = new RawIO;
            $rawIO->emergency("Fatal: $message (Code: $code)");

            if (is_int($code)) {
                if ($code > 255) {
                    $code = 255;
                }

                exit($code);
            }

            exit(1);
        } finally {
            if (is_int($result)) {
                if ($result > 255) {
                    $result = 255;
                }

                exit($result);
            }

            exit(0);
        }
    }

    /**
     * Configures the application.
     *
     * @param array $config The application configuration
     * @return void
     * @throws \InvalidArgumentException
     */
    public function init(array $config)
    {
        Assert::keyExists($config, 'name', 'The console application requires a name!');
        Assert::string($config['name'], 'Expected a string as application name, got %s!');

        Assert::keyExists($config, 'version', 'The console application requires a version!');
        Assert::string($config['version'], 'Expected a string as application version, got %s!');

        $this->setName($config['name']);
        $this->setVersion($config['version']);

        if (array_key_exists('displayName', $config)) {
            Assert::string($config['displayName'], 'Expected a string as display name, got %s');
            $this->setDisplayName($config['displayName']);
        }

        if (array_key_exists('helpText', $config)) {
            Assert::string($config['helpText'], 'Expected a string as help text, got %s!');
            $this->setHelp($config['helpText']);
        }

        if (array_key_exists('debugMode', $config)) {
            Assert::boolean($config['debugMode'], 'Expected a boolean as debug mode, got %s!');
            $this->setDebug($config['debugMode']);
        }

        if (array_key_exists('commands', $config)) {
            Assert::isArray($config['commands'], 'Expected an array of commands, got %s!');
            Assert::allString($config['commands'], 'Expected an array of commands as string!');

            foreach ($config['commands'] as $handlerClass) {
                $this->addCommand($handlerClass);
            }
        }

        if (array_key_exists('options', $config)) {
            Assert::isArray($config['options'], 'Expected an array of options, got %s!');

            foreach ($config['options'] as $optionConfig) {
            }
        }
    }

    public function addCommand(string $class)
    {
        Assert::classExists($class, "The command handler class '$class' does not exist!");
        Assert::subclassOf($class, AbstractCommand::class, "The command handler class '$class' does not inherit from 'MCStreetguy\\SmartConsole\\Command\\AbstractCommand'!");

        $reflector = new \ReflectionClass($class);

        $className = $reflector->getShortName();
        
        Assert::endsWith($className, 'Command', "The command handler class '$class' has an invalid name!");

        $commandName = str_replace('Command', '', $className);
        $command = $this->beginCommand($commandName);

        $classDocBlock = DocBlockFactory::create($reflector->getDocComment());

        $command->setDescription($classDocBlock->getDescription());
        $command->setHandler(function () use ($class) {
            return new $class;
        });

        $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC | ~\ReflectionMethod::IS_STATIC);

        Assert::notEmpty($methods, "The command handler class '$class' defines no valid methods!");

        $actionMethods = array_filter($methods, function (\ReflectionMethod $elem) {
            return (bool) preg_match('/Action$/', $elem->getName());
        });

        Assert::notEmpty($actionMethods, "The command handler class '$class' defines no valid action methods!");

        $isSimpleCommand = (count($methods) === 1);

        $annotationReader = new AnnotationReader();

        foreach ($actionMethods as $method) {
            $cmdName = str_replace('Action', '', $method->getName());

            $subCommand = $command->beginSubCommand($cmdName);
            $subCommand->setHandlerMethod("${cmdName}Cmd");

            if ($isSimpleCommand === true) {
                $subCommand->markDefault();
                $subCommand->markAnonymous();
            } else {
                if ($annotationReader->getMethodAnnotation($method, DefaultCommand::class) !== null) {
                    $subCommand->markDefault();

                    if ($annotationReader->getMethodAnnotation($method, AnonymousCommand::class) !== null) {
                        $subCommand->markAnonymous();
                    }
                }
            }

            $params = $method->getParameters();

            foreach ($params as $parameter) {
                $name = $parameter->getName();
                $description = null;
                $optionShortName = null;
                $defaultValue = $parameter->getDefaultValue();

                $paramTags = $methodDocBlock->getTagsByName('param');
                $paramTags = array_filter($paramTags, function (Param $elem) use ($name) {
                    return ($elem->getVariableName() === $name);
                });

                if (!empty($paramTags)) {
                    $paramTag = $paramTags[0];
                    $description = $paramTag->getDescription();
                }

                if ($parameter->isOptional()) {
                    $optionName = preg_replace_callback('/(?<=.)([A-Z])/', function ($matches) {
                        return '-' . strtolower($matches[1]);
                    }, $name);

                    $flags = Option::PREFER_LONG_NAME;

                    if ($parameter->hasType()) {
                        $type = $parameter->getType();
                    } else {
                        $type = gettype($defaultValue);
                    }

                    switch ((string) $type) {
                        case 'boolean':
                            $flags = $flags | Option::BOOLEAN | Option::NO_VALUE;
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
                            throw new ConfigurationException(
                                "Option '$name' in subcommand '$cmdName' has an invalid type!",
                                1538600675
                            );
                    }

                    if ($parameter->isVariadic()) {
                        $flags = $flags | Option::MULTI_VALUED;
                    }

                    $subCommand->addOption($optionName, null, $flags, $description, $defaultValue);
                } else {
                    $flags = Argument::REQUIRED;

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

                    $subCommand->addArgument($name, $flags, $description, $defaultValue);
                }
            }

            $subCommand->end();
        }
    }
}
