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

use Comely\App\Exception\AppDirectoryException;
use Comely\Filesystem\Directory;
use Comely\Filesystem\Exception\PathException;
use Comely\Filesystem\Exception\PathNotExistException;

/**
 * Class Directories
 * @package Comely\App
 */
class Directories
{
    /** @var AppKernel */
    private $appKernel;
    /** @var Directory */
    private $root;
    /** @var null|Directory */
    private $config;
    /** @var null|Directory */
    private $storage;
    /** @var null|Directory */
    private $langs;
    /** @var null|Directory */
    private $templates;
    /** @var null|Directory */
    private $cache;
    /** @var null|Directory */
    private $compiler;
    /** @var null|Directory */
    private $logs;
    /** @var null|Directory */
    private $sessions;
    /** @var null|Directory */
    private $uploads;

    /**
     * Directories constructor.
     * @param AppKernel $appKernel
     * @param Directory $root
     */
    public function __construct(AppKernel $appKernel, Directory $root)
    {
        $this->appKernel = $appKernel;
        $this->root = $root;
    }

    /**
     * @return Directory
     */
    public function root(): Directory
    {
        return $this->root;
    }

    /**
     * @return Directory
     * @throws AppDirectoryException
     */
    public function config(): Directory
    {
        if (!$this->config) {
            $this->config = $this->dir("config");
        }

        return $this->config;
    }

    /**
     * @return Directory
     * @throws AppDirectoryException
     */
    public function storage(): Directory
    {
        if (!$this->storage) {
            $this->storage = $this->dir("storage");
        }

        return $this->storage;
    }

    /**
     * @return Directory
     * @throws AppDirectoryException
     */
    public function uploads(): Directory
    {
        if (!$this->uploads) {
            $this->uploads = $this->dir("uploads", true);
        }

        return $this->uploads;
    }

    /**
     * @return Directory
     * @throws AppDirectoryException
     */
    public function langs(): Directory
    {
        if (!$this->langs) {
            $this->langs = $this->dir("langs");
        }

        return $this->langs;
    }

    /**
     * @return Directory
     * @throws AppDirectoryException
     */
    public function templates(): Directory
    {
        if (!$this->templates) {
            $this->templates = $this->dir("templates");
        }

        return $this->templates;
    }

    /**
     * @return Directory
     * @throws AppDirectoryException
     */
    public function cache(): Directory
    {
        if (!$this->cache) {
            $this->cache = $this->dir("cache", true);
        }

        return $this->cache;
    }

    /**
     * @return Directory
     * @throws AppDirectoryException
     */
    public function compiler(): Directory
    {
        if (!$this->compiler) {
            $this->compiler = $this->dir("compiler", true);
        }

        return $this->compiler;
    }

    /**
     * @return Directory
     * @throws AppDirectoryException
     */
    public function logs(): Directory
    {
        if (!$this->logs) {
            $this->logs = $this->dir("logs", true);
        }

        return $this->logs;
    }

    /**
     * @return Directory
     * @throws AppDirectoryException
     */
    public function sessions(): Directory
    {
        if (!$this->sessions) {
            $this->sessions = $this->dir("sessions", true);
        }

        return $this->sessions;
    }

    /**
     * @param string $prop
     * @param bool $checkIsWritable
     * @return Directory
     * @throws AppDirectoryException
     */
    private function dir(string $prop, bool $checkIsWritable = false): Directory
    {
        $directoryPath = $this->appKernel->constant("dir_" . $prop);
        if (!$directoryPath) {
            throw new AppDirectoryException(sprintf('No directory const defined for %s', strtoupper($prop)));
        }

        try {
            $directory = $this->root->dir($directoryPath, false);

            if (!$directory->permissions()->read()) {
                throw new AppDirectoryException(sprintf('Directory for %s is not readable', strtoupper($prop)));
            }

            if ($checkIsWritable && !$directory->permissions()->write()) {
                throw new AppDirectoryException(sprintf('Directory for %s is not writable', strtoupper($prop)));
            }

            return $directory;
        } catch (PathNotExistException $e) {
            throw new AppDirectoryException(
                sprintf('Directory defined for %s does not exist in project root', strtoupper($prop))
            );
        } catch (PathException $e) {
            $this->appKernel->errorHandler()->triggerIfDebug($e, E_USER_WARNING);
            throw new AppDirectoryException(sprintf('Failed to load directory for %s', strtoupper($prop)));
        }
    }
}