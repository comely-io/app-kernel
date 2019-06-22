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
use Comely\Filesystem\Directory;
use Comely\Filesystem\Exception\PathException;

/**
 * Class Bootstrapper
 * @package Comely\App
 * @property-read Directory $rootDirectory
 * @property-read null|string $env
 * @property-read bool $dev
 * @property-read bool $loadCachedConfig
 */
class Bootstrapper
{
    /** @var Directory */
    private $rootDirectory;
    /** @var string */
    private $env;
    /** @var bool */
    private $dev;
    /** @var bool */
    private $loadCachedConfig;

    /**
     * Bootstrapper constructor.
     * @param string $rootPath
     * @throws AppBootstrapException
     */
    public function __construct(string $rootPath)
    {
        try {
            $this->rootDirectory = new Directory($rootPath);
        } catch (PathException $e) {
            throw new AppBootstrapException('Invalid root directory path');
        }

        $this->dev = false;
        $this->loadCachedConfig = false;
    }

    /**
     * @param string $prop
     * @return mixed
     */
    public function __get(string $prop)
    {
        switch ($prop) {
            case "rootDirectory":
            case "env":
            case "dev":
            case "loadCachedConfig":
                return $this->$prop;
        }

        throw new \DomainException('Cannot get value of inaccessible property');
    }

    /**
     * @param string $env
     * @return Bootstrapper
     * @throws AppBootstrapException
     */
    public function env(string $env): self
    {
        if (!preg_match('/^\w{3,16}$/', $env)) {
            throw new AppBootstrapException('Invalid environment name');
        }

        $this->env = $env;
        return $this;
    }

    /**
     * @param bool $isDevMode
     * @return Bootstrapper
     */
    public function dev(bool $isDevMode = true): self
    {
        $this->dev = $isDevMode;
        return $this;
    }

    /**
     * @param bool $cachedConfig
     * @return Bootstrapper
     */
    public function loadCachedConfig(bool $cachedConfig = true): self
    {
        $this->loadCachedConfig = $cachedConfig;
        return $this;
    }
}