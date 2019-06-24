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

use Comely\Cache\Cache;
use Comely\Http\Exception\ServiceNotConfiguredException;
use Comely\Http\Router;
use Comely\Sessions\Sessions;
use Comely\Sessions\Storage\SessionDirectory;

/**
 * Class Services
 * @package Comely\App
 */
class Services
{
    /** @var AppKernel */
    private $appKernel;
    /** @var Cache */
    private $cache;
    /** @var Router */
    private $router;
    /** @var Sessions */
    private $sessions;

    /**
     * Services constructor.
     * @param AppKernel $appKernel
     */
    public function __construct(AppKernel $appKernel)
    {
        $this->appKernel = $appKernel;
    }

    /**
     * @return Cache
     * @throws ServiceNotConfiguredException
     * @throws \Comely\Cache\Exception\ConnectionException
     */
    public function cache(): Cache
    {
        if (!$this->cache) {
            $cacheConfig = $this->appKernel->config()->services()->cache();
            if (!$cacheConfig) {
                throw new ServiceNotConfiguredException('Cache service is not configured');
            }

            if (!$cacheConfig->engine) {
                throw new ServiceNotConfiguredException('Cache service is disabled; No engine configured');
            }

            $cache = new Cache();
            $cache->servers()->add($cacheConfig->engine, $cacheConfig->host, $cacheConfig->port);
            $cache->connect();
            $this->cache = $cache;
        }

        return $this->cache;
    }

    /**
     * @return Sessions
     * @throws Exception\AppDirectoryException
     * @throws \Comely\Sessions\Exception\StorageException
     */
    public function sessions(): Sessions
    {
        if (!$this->sessions) {
            $sessionsDirectory = new SessionDirectory($this->appKernel->dirs()->sessions());
            $sessions = new Sessions($sessionsDirectory);
            $this->sessions = $sessions;
        }

        return $this->sessions;
    }

    /**
     * @return Router
     * @throws \Comely\Http\Exception\RouterException
     */
    public function router(): Router
    {
        if (!$this->router) {
            $this->router = new Router();
        }

        return $this->router;
    }

    public function translator()
    {
        // Todo: Translator component
    }

    public function mailer()
    {
        // Todo: Mailer component
    }
}