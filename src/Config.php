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

use Comely\App\Config\DbConfig;
use Comely\App\Config\ServicesConfig;
use Comely\App\Config\Site;
use Comely\App\Exception\AppConfigException;
use Comely\Utils\OOP\ObjectMapper\Exception\ObjectMapperException;
use Comely\Yaml\Exception\YamlException;
use Comely\Yaml\Yaml;

/**
 * Class Config
 * @package Comely\App
 */
class Config
{
    /** @var string */
    private $env;
    /** @var string */
    private $timeZone;
    /** @var Site */
    private $site;
    /** @var array */
    private $dbs;
    /** @var ServicesConfig */
    private $services;

    /**
     * Config constructor.
     * @param AppKernel $appKernel
     * @param string $env
     * @throws AppConfigException
     * @throws Exception\AppDirectoryException
     */
    public function __construct(AppKernel $appKernel, string $env)
    {
        $this->env = $env;

        try {
            $configFilePath = $appKernel->dirs()->config()
                ->suffix(sprintf('env%s%s.yml', DIRECTORY_SEPARATOR, $env));
            $config = Yaml::Parse($configFilePath)
                ->eol("\n")
                ->evalNulls(true)
                ->evalBooleans(true)
                ->generate();
        } catch (YamlException $e) {
            if ($appKernel->dev()) {
                ErrorHandler::Exception2Error($e);
            }

            throw new AppConfigException('Failed to parse app YAML configuration files');
        }

        // TimeZone
        $this->timeZone = $config["time_zone"] ?? $config["timeZone"] ?? null;
        if (!is_string($this->timeZone) || !$this->timeZone) {
            throw new AppConfigException('Invalid configuration time zone');
        }

        // Site
        $siteConfig = $config["site"] ?? null;
        if (!is_array($siteConfig)) {
            throw new AppConfigException('Configuration node "site" not found');
        }

        try {
            $this->site = new Site($config["site"] ?? null);
        } catch (ObjectMapperException $e) {
            throw new AppConfigException($e->getMessage());
        }

        // Databases
        $this->dbs = [];
        $dbsConfig = $config["databases"] ?? null;
        if (is_array($dbsConfig)) {
            $dbConfigPos = 0;
            foreach ($dbsConfig as $tag => $dbConfig) {
                $dbConfigPos++;
                if (!is_string($tag) || !preg_match('/^[\w\-]{2,16}$/', $tag)) {
                    throw new AppConfigException(sprintf('Invalid database block prop/tag at pos %d', $dbConfigPos));
                }

                $tag = strtolower($tag);
                if (!is_array($dbConfig)) {
                    throw new AppConfigException(sprintf('Database "%s" block is invalid', $tag));
                }

                try {
                    $dbConfig = new DbConfig($dbConfig);
                    $this->dbs[$tag] = $dbConfig;
                } catch (ObjectMapperException $e) {
                    throw new AppConfigException(sprintf('[Database:[%s]] %s', $tag, $e->getMessage()));
                }
            }
        }

        // Services
        $servicesConfig = $config["services"] ?? [];
        if (is_array($servicesConfig)) {
            $this->services = new ServicesConfig($servicesConfig);
        }
    }

    /**
     * @return string
     */
    public function env(): string
    {
        return $this->env;
    }

    /**
     * @return Site
     */
    public function site(): Site
    {
        return $this->site;
    }

    /**
     * @param string $tag
     * @return DbConfig|null
     */
    public function db(string $tag): ?DbConfig
    {
        return $this->dbs[strtolower($tag)] ?? null;
    }
}