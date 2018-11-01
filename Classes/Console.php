<?php

namespace MCStreetguy\SmartConsole;

use DI\Container;
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
use MCStreetguy\SmartConsole\Exceptions\UnsupportedFeatureException;
use MCStreetguy\SmartConsole\Utility\Analyzer;
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
use MCStreetguy\SmartConsole\Annotations\Application\LogDir;
use MCStreetguy\SmartConsole\Utility\Logger;
use MCStreetguy\SmartConsole\Utility\Misc\LoggerFactory;

class Console extends DefaultApplicationConfig
{
    /**
     * @var Container
     */
    protected static $container;

    /**
     * The log-directory of the application.
     * @var string|null
     */
    protected static $logDir;

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

    /**
     * Run the application.
     *
     * @param ApplicationConfig|null $config Optional configuration object to use for execution.
     * @return void
     */
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

    /**
     * Register the internal error and exception handlers.
     *
     * @return void
     */
    public static function registerHandlers()
    {
        $io = new RawIO;
        $logger = LoggerFactory::build();

        set_exception_handler(function (\Throwable $e) use ($io, $logger) {
            if ($e instanceof \Error || $e instanceof \ErrorException) {
                $type = 'Fatal';
            } else {
                $type = get_class($e);
            }

            $message = $e->getMessage();
            $code = $e->getCode();

            $io->emergency($msg = "$type: $message");
            ($logger !== null) && $logger->addEmergency($msg);
            die($code);
        });

        set_error_handler(function ($code, $msg) use ($io, $logger) {
            $io->emergency("Fatal: $msg");
            ($logger !== null) && $logger->addEmergency($msg);
            die($code);
        }, E_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);

        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 1);
    }

    /**
     * Get the dependency container.
     *
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
     * Prepare the configuration instance with common settings.
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->addOption(
            'assume-yes',
            'y',
            Option::NO_VALUE | Option::BOOLEAN,
            'Assume yes as answer for all confirmations'
        );
    }

    /**
     * Analyse the current inheriting class in order to recieve the configuration options automatically from code.
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

        $helpText = $classSummary = $classDocBlock->getSummary();
        if (!empty($helpText)) {
            $classDescription = $classDocBlock->getDescription();

            if (!empty($classDescription)) {
                $helpText .= PHP_EOL . PHP_EOL . $classDescription;
            }

            $config['helpText'] = HelpTextUtility::convertToHelpText($helpText);
        }

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

        /** @var LogDir|null $logDirAnnotation */
        $logDirAnnotation = $this->annotationReader->getClassAnnotation($reflector, LogDir::class);
        if ($logDirAnnotation !== null) {
            $config['logDir'] = $logDirAnnotation->getPath();
        }

        $debugModeAnnotation = $this->annotationReader->getClassAnnotation($reflector, DebugMode::class);
        if ($debugModeAnnotation !== null) {
            $config['debugMode'] = true;
        }

        return $this->initFromConfig($config);
    }

    /**
     * Configure the application.
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

        if (array_key_exists('logDir', $config)) {
            Assert::string($config['logDir'], 'Expected a string as logging directory, got %s!');
            $this->setLogDir($config['logDir']);
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

    /**
     * Add a command to the configuration.
     *
     * @param string $class The command handler class to analyze
     * @return mixed
     */
    public function addCommand(string $class)
    {
        Assert::classExists($class, "Command handler class '%s' could not be found!");

        return $this->analyzer->addCommand($class, $this);
    }

    /**
     * Set the logging directory of the application.
     *
     * @param string $dir The logging-directory path
     * @return self
     */
    public static function setLogDirPath(string $dir) : self
    {
        Assert::directory($dir, "Invalid log-directory: %s!");

        self::$logDir = $dir;

        return $this;
    }

    /**
     * Get the logging-directory of the application.
     *
     * @return string|null
     */
    public static function getLogDirPath()
    {
        return self::$logDir;
    }

    /**
     * Set the logging directory of the application.
     *
     * @param string $dir The logging-directory path
     * @return self
     */
    public function setLogDir(string $dir)
    {
        return self::setLogDirPath($dir);
    }

    /**
     * Get the logging-directory of the application.
     *
     * @return string|null
     */
    public function getLogDir()
    {
        return self::getLogDirPath();
    }
}
