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

namespace Comely\App\Http;

/**
 * Class Cookies
 * @package Comely\App\Http
 */
class Cookies
{
    /** @var int */
    private $expire;
    /** @var string */
    private $path;
    /** @var string */
    private $domain;
    /** @var bool */
    private $secure;
    /** @var bool */
    private $httpOnly;

    /**
     * Cookies constructor.
     */
    public function __construct()
    {
        $this->expire = 604800; // 1 week
        $this->path = "/";
        $this->domain = "";
        $this->secure = true;
        $this->httpOnly = true;
    }

    /**
     * @param int $seconds
     * @return Cookies
     */
    public function expire(int $seconds): self
    {
        $this->expire = $seconds;
        return $this;
    }

    /**
     * @param string $path
     * @return Cookies
     */
    public function path(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @param string $domain
     * @return Cookies
     */
    public function domain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @param bool $https
     * @return Cookies
     */
    public function secure(bool $https): self
    {
        $this->secure = $https;
        return $this;
    }

    /**
     * @param bool $httpOnly
     * @return Cookies
     */
    public function httpOnly(bool $httpOnly): self
    {
        $this->httpOnly = $httpOnly;
        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function set(string $name, string $value): bool
    {
        return setcookie(
            $name,
            $value,
            time() + $this->expire,
            $this->path,
            $this->domain,
            $this->secure,
            $this->httpOnly
        );
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function get(string $name): ?string
    {
        return $_COOKIE[$name] ?? null;
    }
}