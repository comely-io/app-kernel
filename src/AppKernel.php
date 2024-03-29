<?php
/**
 * This file is a part of "comely-io/app-kernel" package.
 * https://github.com/comely-io/app-kernel
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comely-io/app-kernel/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\App;

use Comely\App\Exception\AppBootstrapException;
use Comely\App\Exception\AppConfigException;
use Comely\App\Traits\NotCloneableTrait;
use Comely\App\Traits\NotSerializableTrait;
use Comely\Cache\Cache;
use Comely\Filesystem\Exception\PathException;
use Comely\Filesystem\Exception\PathNotExistException;
use Comely\Filesystem\Exception\PathPermissionException;
use Comely\Mailer\Mailer;
use Comely\Sessions\ComelySession;
use Comely\Sessions\Exception\ComelySessionException;

/**
 * Class AppKernel
 * @package Comely\App
 */
abstract class AppKernel implements \Serializable
{
    /** @var string App Name, Extending class should change these constant */
    public const NAME = "Comely App Kernel";
    /** string Comely App Kernel Version (Major.Minor.Release-Suffix) */
    public const VERSION = "2019.310";
    /** int Comely App Kernel Version (Major . Minor . Release) */
    public const VERSION_ID = 201931000;

    protected const DIR_CONFIG = null;
    protected const DIR_STORAGE = null;
    protected const DIR_UPLOADS = null;
    protected const DIR_LANGS = null;
    protected const DIR_TEMPLATES = null;
    protected const DIR_CACHE = null;
    protected const DIR_COMPILER = null;
    protected const DIR_LOGS = null;
    protected const DIR_SESSIONS = null;

    /** @var static */
    private static $instance;

    /** @var bool */
    private $bootstrapped;
    /** @var Config */
    private $config;
    /** @var CipherKeys */
    private $cipherKeys;
    /** @var Databases */
    private $databases;
    /** @var bool */
    private $dev;
    /** @var Directories */
    private $directories;
    /** @var ErrorHandler */
    private $errorHandler;
    /** @var string */
    private $timeZone;
    /** @var Events */
    private $events;
    /** @var Http */
    private $http;
    /** @var bool */
    private $services;
    /** @var Memory */
    private $memory;

    use NotCloneableTrait;
    use NotSerializableTrait;

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }

        throw new \RuntimeException('App kernel has not been bootstrapped');
    }

    /**
     * @param Bootstrapper $bs
     * @return static
     * @throws AppBootstrapException
     * @throws AppConfigException
     * @throws Exception\AppDirectoryException
     */
    public static function Bootstrap(Bootstrapper $bs)
    {
        if (self::$instance) {
            throw new \RuntimeException('App kernel has already been bootstrapped; use getInstance method instead');
        }

        self::$instance = new static($bs);
        return self::$instance;
    }

    /**
     * AppKernel constructor.
     * @param Bootstrapper $bootstrapper
     * @throws AppBootstrapException
     * @throws Exception\AppConfigException
     * @throws Exception\AppDirectoryException
     */
    protected function __construct(Bootstrapper $bootstrapper)
    {
        // Environment variable
        $this->dev = $bootstrapper->dev;

        // Directories structure
        $this->directories = new Directories($this, $bootstrapper->rootDirectory);

        // Initiate Error Handler
        $this->errorHandler = new ErrorHandler($this);

        // Load App Configuration
        $this->configure($bootstrapper);

        // Events
        $this->events = new Events($this);

        // Timezone
        $this->timeZone = $this->config()->timeZone;
        if (!in_array($this->timeZone, \DateTimeZone::listIdentifiers())) {
            throw new AppConfigException('Invalid timezone');
        }

        date_default_timezone_set($this->timeZone);

        // Databases
        $this->databases = new Databases($this);

        // Cipher Keys
        $this->cipherKeys = new CipherKeys($this);

        // Services
        $this->services = new Services($this);

        // Bootstrapped flag
        $this->bootstrapped = true;
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return [static::NAME, static::VERSION];
    }

    /**
     * @param string $const
     * @return mixed
     */
    final public function constant(string $const)
    {
        return @constant('static::' . strtoupper($const));
    }

    /**
     * @return bool
     */
    final public function dev(): bool
    {
        return $this->dev;
    }

    /**
     * @return Config
     */
    final public function config(): Config
    {
        return $this->config;
    }

    /**
     * @return Directories
     */
    final public function dirs(): Directories
    {
        return $this->directories;
    }

    /**
     * @return ErrorHandler
     */
    final public function errorHandler(): ErrorHandler
    {
        return $this->errorHandler;
    }

    /**
     * @return bool
     */
    final public function isBootstrapped(): bool
    {
        return $this->bootstrapped ?? false;
    }

    /**
     * @return Events
     */
    final public function kernelEvents(): Events
    {
        return $this->events;
    }

    /**
     * @return Databases
     */
    final public function databases(): Databases
    {
        return $this->databases;
    }

    /**
     * @return CipherKeys
     */
    final public function cipherKeys(): CipherKeys
    {
        return $this->cipherKeys;
    }

    /**
     * @return Services
     */
    final public function services(): Services
    {
        return $this->services;
    }

    /**
     * @param Bootstrapper $bootstrapper
     * @throws AppBootstrapException
     * @throws Exception\AppConfigException
     * @throws Exception\AppDirectoryException
     */
    final private function configure(Bootstrapper $bootstrapper): void
    {
        if (!$bootstrapper->env) {
            throw new AppBootstrapException('No value for bootstrap prop "env"');
        }

        $cachedConfigFilename = sprintf('bootstrap.config.env_%s.php.cache', $bootstrapper->env);

        if ($bootstrapper->loadCachedConfig) {
            try {
                $cachedConfig = $this->dirs()->cache()
                    ->file($cachedConfigFilename)
                    ->read();

                $cachedConfig = unserialize(base64_decode($cachedConfig), [
                    "allowed_classes" => [
                        'Comely\App\Config',
                        'Comely\App\Config\CacheServiceConfig',
                        'Comely\App\Config\CipherConfig',
                        'Comely\App\Config\DbConfig',
                        'Comely\App\Config\HttpCookiesConfig',
                        'Comely\App\Config\HttpServiceConfig',
                        'Comely\App\Config\MailerConfig',
                        'Comely\App\Config\MailerSMTPConfig',
                        'Comely\App\Config\ServicesConfig',
                        'Comely\App\Config\SessionsServiceConfig',
                        'Comely\App\Config\SiteConfig',
                        'Comely\App\Config\TranslatorServiceConfig',
                    ]
                ]);

                if (!$cachedConfig || !$cachedConfig instanceof Config) {
                    throw new \RuntimeException('Failed to unserialize cached config');
                }

                $this->config = $cachedConfig;
                return;
            } catch (PathNotExistException $e) {
                // Cached config file does not exist
            } catch (PathPermissionException $e) {
                trigger_error(
                    sprintf('Cached configuration file "%s" is not readable in cache directory', $cachedConfigFilename),
                    E_USER_WARNING
                );
            } catch (PathException $e) {
                $this->errorHandler->triggerIfDebug($e, E_USER_WARNING);
                trigger_error('An error occurred while reading cached configuration', E_USER_WARNING);
            }
        }

        $this->config = new Config($this, $bootstrapper->env);

        if ($bootstrapper->loadCachedConfig) {
            try {
                $this->dirs()->cache()->write(
                    $cachedConfigFilename,
                    base64_encode(serialize($this->config)),
                    false,
                    true
                );
            } catch (PathPermissionException $e) {
                trigger_error('Cache directory is not writable; Cannot cache configuration', E_USER_WARNING);
            } catch (PathException $e) {
                $this->errorHandler->triggerIfDebug($e, E_USER_WARNING);
                trigger_error('Failed to write cached configuration file', E_USER_WARNING);
            }
        }
    }

    /**
     * @return Http
     */
    public function http(): Http
    {
        if (!$this->http) {
            $this->http = new Http($this);
        }

        return $this->http;
    }

    /**
     * @param string $id
     * @return ComelySession
     * @throws ComelySessionException
     * @throws Exception\AppDirectoryException
     * @throws Exception\ServiceNotConfiguredException
     * @throws \Comely\Sessions\Exception\StorageException
     */
    public function session(string $id): ComelySession
    {
        try {
            return $this->services->sessions()->resume($id);
        } catch (ComelySessionException $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        return $this->services->sessions()->start();
    }

    /**
     * @return Cache
     * @throws Exception\ServiceNotConfiguredException
     * @throws \Comely\Cache\Exception\ConnectionException
     */
    public function cache(): Cache
    {
        return $this->services->cache();
    }

    /**
     * @return Mailer
     * @throws Exception\ServiceNotConfiguredException
     * @throws \Comely\Mailer\Exception\InvalidEmailAddrException
     */
    public function mailer(): Mailer
    {
        return $this->services->mailer();
    }

    /**
     * @return Memory
     */
    public function memory(): Memory
    {
        if ($this->memory) {
            return $this->memory;
        }

        $this->memory = new Memory();

        try {
            $cache = $this->services->cache();
            $this->memory->caching($cache);
        } catch (\Exception $e) {
        }

        return $this->memory;
    }
}