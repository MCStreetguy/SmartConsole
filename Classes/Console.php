<?php

namespace MCStreetguy\SmartConsole;

use DI\ContainerBuilder;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use MCStreetguy\SmartConsole\Annotations\App\DebugMode;
use MCStreetguy\SmartConsole\Annotations\App\DisplayName;
use MCStreetguy\SmartConsole\Annotations\App\Version;
use MCStreetguy\SmartConsole\Annotations\Command\AnonymousCommand;
use MCStreetguy\SmartConsole\Annotations\Command\DefaultCommand;
use MCStreetguy\SmartConsole\Command\AbstractCommand;
use MCStreetguy\SmartConsole\Exceptions\ConfigurationException;
use MCStreetguy\SmartConsole\Utility\Helper\StringHelper;
use MCStreetguy\SmartConsole\Utility\Misc\HelpTextUtility;
use MCStreetguy\SmartConsole\Utility\RawIO;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlockFactory;
use Webmozart\Assert\Assert;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Config\DefaultApplicationConfig;
use Webmozart\Console\ConsoleApplication;

class Console extends DefaultApplicationConfig
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var AnnotationReader
     */
    protected $annotationReader;

    /**
     * @var DocBlockFactory
     */
    protected $docBlockFactory;

    /**
     * @var array
     */
    protected static $factoryDefinitions = [];

    public static function run(ApplicationConfig $config = null)
    {
        if ($config === null) {
            $config = new static();
        }

        if ($config->isDebug()) {
            (new ConsoleApplication($config))->run();
            exit;
        }

        $config->setTerminateAfterRun(false);
        $config->setCatchExceptions(false);

        $code = 0;

        try {
            $cli = new ConsoleApplication($config);
            $result = $cli->run();

            if (!empty($result) && is_int($result)) {
                $code = $result;
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = $e->getCode();

            $rawIO = new RawIO;
            $rawIO->emergency("Fatal: $message (Code: $code)");

            if (empty($code) || !is_int($code)) {
                $code = 1;
            }
        } finally {
            if (is_int($code) && $code > 255) {
                $code = 255;
            }

            exit($code);
        }
    }

    public function execute()
    {
        static::run($this);
    }

    /**
     * @inheritDoc
     */
    public function __construct($name = null, $version = null)
    {
        # Dependency Container
        $factory = new ContainerBuilder();
        $factory->useAnnotations(true);
        $factory->useAutowiring(true);

        $container = $factory->build();

        if (is_array(static::$factoryDefinitions) && !empty(static::$factoryDefinitions)) {
            foreach (static::$factoryDefinitions as $target => $definition) {
                $container->set($target, $definition);
            }
        }

        $this->container = $container;

        # Annotation Reader
        AnnotationRegistry::registerLoader('class_exists');
        $this->annotationReader = new AnnotationReader();

        # DocBlockParser
        $this->docBlockFactory = DocBlockFactory::createInstance();

        # Further init
        parent::__construct($name, $version);
    }

    /**
     * Analyses the current inheriting class in order to recieve the configuration options automatically from code.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public function init()
    {
        $config = [];
        $reflector = new \ReflectionClass(static::class);

        $className = $reflector->getShortName();
        $config['name'] = $appName = StringHelper::camelToSnakeCase($className);

        $classDocBlock = $reflector->getDocComment();
        Assert::notEmpty($classDocBlock, "Cannot auto-init '$className' as it contains no valid doc-block to analyze!");
        $classDocBlock = $this->docBlockFactory->create($classDocBlock);

        /** @var Version|null $versionAnnotation */
        $versionAnnotation = $this->annotationReader->getClassAnnotation($reflector, Version::class);
        if ($versionAnnotation !== null) {
            $config['version'] = $versionAnnotation->getVersion();
        }

        /** @var DisplayName|null $displayNameAnnotation */
        $displayNameAnnotation = $this->annotationReader->getClassAnnotation($reflector, DisplayName::class);
        if ($displayNameAnnotation !== null) {
            $config['displayName'] = $displayNameAnnotation->getName();
        }

        $helpText = $classSummary = $classDocBlock->getSummary();
        if (!empty($helpText)) {
            $classDescription = $classDocBlock->getDescription();

            if (!empty($classDescription)) {
                $helpText .= PHP_EOL . PHP_EOL . $classDescription;
            }

            $config['helpText'] = HelpTextUtility::convertToHelpText($helpText);
        }

        $debugModeAnnotation = $this->annotationReader->getClassAnnotation($reflector, DebugMode::class);
        if ($debugModeAnnotation !== null) {
            $config['debugMode'] = true;
        }

        return $this->initFromConfig($config);
    }

    /**
     * Configures the application.
     *
     * @param array $config The application configuration
     * @return void
     * @throws \InvalidArgumentException
     */
    public function initFromConfig(array $config)
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

        Assert::endsWith($className, 'Command', "The command handler class '$class' has an invalid name: '%s'!");

        $commandName = str_replace('Command', '', $className);
        $commandName = StringHelper::camelToSnakeCase($commandName);

        $command = $this->beginCommand($commandName);

        $classDocBlock = $reflector->getDocComment();

        Assert::notEmpty($classDocBlock, "The command handler class '$class' is missing a descriptive docblock!");

        $classDocBlock = $this->docBlockFactory->create($classDocBlock);

        $summary = $classDocBlock->getSummary();

        Assert::notEmpty($summary, "The command handler doc-block of '$class' is missing a summary!");

        $command->setDescription($summary);

        if (!empty($description = (string) $classDocBlock->getDescription())) {
            $description = HelpTextUtility::convertToHelpText($description);
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

        $isSimpleCommand = (count($actionMethods) === 1);

        foreach ($actionMethods as $method) {
            $cmdName = str_replace('Action', '', $method->getName());

            $subCommand = $command->beginSubCommand(StringHelper::camelToSnakeCase($cmdName));
            $subCommand->setHandlerMethod("${cmdName}Cmd");

            if ($isSimpleCommand === true) {
                $subCommand->markDefault();
                $subCommand->markAnonymous();
            } else {
                if ($this->annotationReader->getMethodAnnotation($method, DefaultCommand::class) !== null) {
                    $subCommand->markDefault();

                    if ($this->annotationReader->getMethodAnnotation($method, AnonymousCommand::class) !== null) {
                        $subCommand->markAnonymous();
                    }
                }

                $methodDocBlock = $method->getDocComment();

                Assert::notEmpty($methodDocBlock, "The action method '$cmdName' in class '$class' is missing a descriptive docblock!");

                $methodDocBlock = $this->docBlockFactory->create($methodDocBlock);

                $commandSummary = $methodDocBlock->getSummary();

                Assert::notEmpty($commandSummary, "The action method doc-block for '$cmdName' in '$class' is missing a summary!");

                $subCommand->setDescription($commandSummary);

                if (!empty($commandDescription = (string) $methodDocBlock->getDescription())) {
                    $commandDescription = HelpTextUtility::convertToHelpText($commandDescription);
                    $subCommand->setHelp($commandDescription);
                }
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

                if ($parameter->isOptional()) {
                    $defaultValue = $parameter->getDefaultValue();

                    $optionName = StringHelper::camelToSnakeCase($name);
                    $shortNameMap = array_filter($this->annotationReader->getMethodAnnotations($method), function ($elem) use ($optionName) {
                        return ($elem instanceof ShortName) && ($elem->getOption() === $optionName);
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
                            throw new ConfigurationException(
                                "Option '$name' in subcommand '$cmdName' has an invalid type!",
                                1538600675
                            );
                    }

                    if ($parameter->isVariadic()) {
                        $flags = $flags | Option::MULTI_VALUED;
                    }

                    $subCommand->addOption($optionName, $shortName, $flags, $description, $defaultValue);
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

                    $subCommand->addArgument($name, $flags, $description);
                }
            }

            $subCommand->end();
        }
    }
}
