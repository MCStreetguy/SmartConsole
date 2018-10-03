<?php

namespace MCStreetguy\SmartConsole;

use Webmozart\Console\ConsoleApplication;
use Webmozart\Console\Config\DefaultApplicationConfig as ApplicationConfig;
use Webmozart\Assert\Assert;
use MCStreetguy\SmartConsole\Command\AbstractCommand;
use Webmozart\Console\Api\Config\CommandConfig;
use phpDocumentor\Reflection\DocBlockFactory;
use Doctrine\Common\Annotations\AnnotationReader;
use MCStreetguy\SmartConsole\Annotations\DefaultCommand;
use MCStreetguy\SmartConsole\Annotations\AnonymousCommand;
use Webmozart\Console\Api\Args\Format\Argument;
use League\CLImate\Argument\Argument;
use MCStreetguy\SmartConsole\Exceptions\ConfigurationException;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use MCStreetguy\SmartConsole\Annotations\OptionNameMap;
use MCStreetguy\SmartConsole\Annotations\ShortName;

class Console extends ApplicationConfig
{
    /**
     * Configures the application.
     *
     * @param array $config The application configuration
     * @return void
     * @throws \InvalidArgumentException
     */
    public function configure(array $config)
    {
        Assert::keyExists($config, 'name', 'The console application requires a name!');
        Assert::string($config['name'], 'Expected a string as application name, got %s!');

        Assert::keyExists($config, 'version', 'The console application requires a version!');
        Assert::string($config['version'], 'Expected a string as application version, got %s!');

        $this->setName($config['name']);
        $this->setVersion($config['version']);
        
        $this->setTerminateAfterRun(true);
        $this->setCatchExceptions(false);

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
                $this->analyzeCommand($handlerClass);
            }
        }

        if (array_key_exists('options', $config)) {
            Assert::isArray($config['options'], 'Expected an array of options, got %s!');

            foreach ($config['options'] as $optionConfig) {
            }
        }
    }

    protected function analyzeCommand(string $class) : CommandConfig
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
            $methodDocBlock = DocBlockFactory::create($method->getDocComment());
            
            $shortNameMap = $annotationReader->getMethodAnnotation($method, OptionNameMap::class);
            if ($shortNameMap !== null) {
                $shortNameMap = $shortNameMap->getShortNames();
            }

            foreach ($params as $parameter) {
                $name = $parameter->getName();
                $flags = Argument::REQUIRED;
                $description = null;
                $optionShortName = null;
                $defaultValue = $parameter->getDefaultValue();

                if ($parameter->hasType()) {
                    $type = $parameter->getType();

                    switch ($type) {
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

                $paramTags = $methodDocBlock->getTagsByName('param');
                $paramTags = array_filter($paramTags, function (Param $elem) use ($name) {
                    return ($elem->getVariableName() === $name);
                });

                if (!empty($paramTags)) {
                    $paramTag = $paramTags[0];
                    $description = $paramTag->getDescription();
                }

                if ($parameter->isOptional()) {
                    $optionShortName = array_filter($shortNameMap, function (ShortName $elem) use ($name) {
                        return ($elem->getOption() === $name);
                    });

                    if (!empty($optionShortName)) {
                        $optionShortName = $optionShortName[0]->getShortName();
                    }

                    $subCommand->addOption($name, $optionShortName, $flags, $description, $defaultValue);
                } else {
                    $subCommand->addArgument($name, $flags, $description, $defaultValue);
                }
            }
        }

        return $command;
    }
}
