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

namespace Comely\App\Memory;

use Comely\App\Memory;

/**
 * Class Query
 * @package Comely\App\Memory
 * @property-read bool $cache
 * @property-read int $cacheTTL
 * @property-read string $key
 * @property-read string $instanceOf
 * @property-read null|\Closure $callback
 */
class Query
{
    /** @var Memory */
    private $memory;
    /** @var bool */
    private $cache;
    /** @var int */
    private $cacheTTL;
    /** @var string */
    private $key;
    /** @var null|string */
    private $instanceOf;
    /** @var null|\Closure */
    private $callback;

    /**
     * Query constructor.
     * @param Memory $memory
     * @param string $key
     * @param string $instanceOf
     */
    public function __construct(Memory $memory, string $key, string $instanceOf)
    {
        $this->memory = $memory;
        $this->cache = false;
        $this->cacheTTL = 0;
        $this->key = $key;
        $this->instanceOf = $instanceOf;
    }

    /**
     * @param string $prop
     * @return mixed
     */
    public function __get(string $prop)
    {
        switch ($prop) {
            case "cache":
            case "cacheTTL":
            case "key":
            case "instanceOf":
            case "callback":
                return $this->$prop;
        }

        throw new \DomainException('Cannot read inaccessible property');
    }

    /**
     * @param int $ttl
     * @return Query
     */
    public function cache(int $ttl = 0): self
    {
        $this->cache = true;
        $this->cacheTTL = $ttl;
        return $this;
    }

    /**
     * @param \Closure $callback
     * @return Query
     */
    public function callback(\Closure $callback): self
    {
        if (is_callable($callback)) {
            $this->callback = $callback;
        }

        return $this;
    }

    /**
     * @param \Closure|null $callback
     * @return mixed
     */
    public function fetch(?\Closure $callback = null)
    {
        if ($callback) {
            $this->callback($callback);
        }

        return $this->memory->get($this);
    }
}