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

use Comely\App\Exception\ServiceNotConfiguredException;
use Comely\App\Http\Cookies;
use Comely\App\Http\Remote;
use Comely\Http\Router;

/**
 * Class Http
 * @package Comely\App
 */
class Http
{
    /** @var AppKernel */
    private $appKernel;
    /** @var Remote */
    private $remote;
    /** @var null|Cookies */
    private $cookies;

    /**
     * Http constructor.
     * @param AppKernel $appKernel
     */
    public function __construct(AppKernel $appKernel)
    {
        $this->appKernel = $appKernel;
        $this->remote = new Remote();
    }

    /**
     * @return int|null
     */
    public function port(): ?int
    {
        $port = $_SERVER["SERVER_PORT"] ?? null;
        if (!is_null($port)) {
            $port = intval($port);
        }

        return $port;
    }

    /**
     * @return bool
     */
    public function https(): bool
    {
        return isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] ? true : false;
    }

    /**
     * @return Router
     * @throws \Comely\Http\Exception\RouterException
     */
    public function router(): Router
    {
        return $this->appKernel->services()->router();
    }

    /**
     * @return Remote
     */
    public function remote(): Remote
    {
        return $this->remote;
    }

    /**
     * @return Cookies
     * @throws ServiceNotConfiguredException
     */
    public function cookies(): Cookies
    {
        if ($this->cookies) {
            return $this->cookies;
        }

        $cookiesConfig = null;
        $httpConfig = $this->appKernel->config()->services()->http();
        if ($httpConfig) {
            $cookiesConfig = $httpConfig->cookies();
        }

        if (!$cookiesConfig) {
            throw new ServiceNotConfiguredException('Http cookies not configured');
        }

        $cookies = new Cookies();
        $cookies->expire($cookiesConfig->expire)
            ->path($cookiesConfig->path)
            ->domain($cookiesConfig->domain ?? sprintf(".%s", $this->appKernel->config()->site()->domain))
            ->secure($cookiesConfig->secure)
            ->httpOnly($cookiesConfig->httpOnly);

        $this->cookies = $cookies;
        return $this->cookies;
    }
}