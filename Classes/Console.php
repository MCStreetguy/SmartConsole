<?php

namespace MCStreetguy\SmartConsole;

use DI\ContainerBuilder;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use MCStreetguy\SmartConsole\Annotations\Application\DebugMode;
use MCStreetguy\SmartConsole\Annotations\Application\DisplayName;
use MCStreetguy\SmartConsole\Annotations\Application\Version;
use MCStreetguy\SmartConsole\Annotations\Command\AnonymousCommand;
use MCStreetguy\SmartConsole\Annotations\Command\DefaultCommand;
use MCStreetguy\SmartConsole\Command\AbstractCommand;
use MCStreetguy\SmartConsole\Exceptions\ConfigurationException;
use MCStreetguy\SmartConsole\Exceptions\ErrorException;
use MCStreetguy\SmartConsole\Utility\Helper\StringHelper;
use MCStreetguy\SmartConsole\Utility\Misc\HelpTextUtility;
use MCStreetguy\SmartConsole\Utility\RawIO;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlockFactory;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Config\DefaultApplicationConfig;
use Webmozart\Console\ConsoleApplication;
use DI\Container;
use MCStreetguy\SmartConsole\Utility\Analyzer;
use MCStreetguy\SmartConsole\Exceptions\UnsupportedFeatureException;

class Console extends DefaultApplicationConfig
{
    /**
     * @var Container
     */
    protected static $container;

    /**
     * @Inject
     * @var Analyzer
     */
    protected $analyzer;

    /**
     * @Inject
     * @var AnnotationReader
     */
    protected $annotationReader;

    /**
     * @Inject
     * @var DocBlockFactory
     */
    protected $docBlockFactory;

    public static function run(ApplicationConfig $config = null)
    {
        if ($config === null) {
            $config = new static();
        }

        if (!$config->isDebug()) {
            $config->setCatchExceptions(false);

            self::registerHandlers();
        }

        $config->setTerminateAfterRun(false);

        $code = 0;

        $cli = new ConsoleApplication($config);
        $result = $cli->run();

        if (!empty($result) && is_int($result)) {
            $code = $result;
        }

        if (is_int($code) && $code > 255) {
            $code = 255;
        }

        exit($code);
    }

    public static function registerHandlers()
    {
        $io = new RawIO;

        $handler = function (\Exception $e) use ($io) {
            $message = $e->getMessage();
            $code = $e->getCode();

            $io->emergency("Fatal: $message (Code: $code)");
        };

        set_exception_handler($handler);

        set_error_handler(function ($code, $msg) use ($handler) {
            $converted = ErrorException::create($msg, $code);
            
            call_user_func($handler, $converted);
        }, error_reporting());

        error_reporting(~E_ALL);
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 1);
    }

    /**
     * Get the DI-container instance.
     * @return Container
     */
    public static function getContainer() : Container
    {
        // Return the container immediately if it has already been built
        if (!empty(static::$container)) {
            return static::$container;
        }

        $factory = new ContainerBuilder();

        // Enable annotation injection and autowiring on the container
        $factory->useAnnotations(true);
        $factory->useAutowiring(true);

        // Add library definition file
        $factory->addDefinitions(__DIR__ . '/Utility/Misc/FactoryDefinitions.php');
        
        // Add user definition files if available
        if (property_exists(static::class, 'factoryDefinitions') &&
            !empty(static::$factoryDefinitions) &&
            is_array(static::$factoryDefinitions)
        ) {
            foreach (static::$factoryDefinitions as $file) {
                Assert::file($file, "The definition source '%s' is no file!");
                Assert::readable($file, "The definition source '%s' is not readable!");
                
                $factory->addDefinitions($file);
            }
        }

        return (static::$container = $factory->build());
    }

    /**
     * @inheritDoc
     */
    public function __construct($name = null, $version = null)
    {
        # Prerequisites
        AnnotationRegistry::registerLoader('class_exists');

        # Dependencies
        $container = static::getContainer();
        $container->injectOn($this);

        # Further init
        parent::__construct($name, $version);
    }

    /**
     * Execute the application.
     */
    public function execute()
    {
        static::run($this);
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
                UnsupportedFeatureException::forFeatureName('global options');
            }
        }
    }

    public function addCommand(string $class)
    {
        Assert::classExists($class, "Command handler class '%s' could not be found!");

        return $this->analyzer->addCommand($class, $this);
    }
}
