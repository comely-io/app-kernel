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

use Comely\App\Memory\Query;
use Comely\Cache\Cache;
use Comely\Cache\Exception\CacheException;

/**
 * Class Memory
 * @package Comely\App
 */
class Memory
{
    /** @var array */
    private $instances;
    /** @var null|Cache */
    private $cache;

    /**
     * Memory constructor.
     */
    public function __construct()
    {
        $this->instances = [];
    }

    /**
     * @param Cache $cache
     * @return Memory
     */
    public function caching(Cache $cache): self
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * @param string $key
     * @param string $instanceOf
     * @return Query
     */
    public function query(string $key, string $instanceOf): Query
    {
        return new Query($this, $key, $instanceOf);
    }

    /**
     * @return void
     */
    public function flush(): void
    {
        $this->instances = [];
    }

    /**
     * @param Query $query
     * @return bool|\Comely\Cache\CachedItem|float|int|mixed|string|null
     */
    public function get(Query $query)
    {
        $key = $query->key;
        $instanceOf = $query->instanceOf;
        $this->validateKey($key);

        // Check in run-time memory
        $object = $this->instances[$key] ?? null;
        if (is_object($object) && is_a($object, $instanceOf)) {
            return $object;
        }

        // Check in Cache
        if ($this->cache && $query->cache) {
            try {
                $cached = $this->cache->get($key, false);
                if (is_object($cached) && is_a($cached, $instanceOf)) {
                    $this->instances[$key] = $cached; // Store in run-time memory
                    return $cached;
                }
            } catch (CacheException $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }

        // Not found, proceed with callback (if any)
        $callback = $query->callback;
        if (is_callable($callback)) {
            $object = call_user_func($callback);
            if (is_object($object)) {
                $this->set($key, $object, $query->cache, $query->cacheTTL);
                return $object;
            }
        }

        return null;
    }

    /**
     * @param string $key
     * @param $object
     * @param bool $cache
     * @param int $ttl
     */
    public function set(string $key, $object, bool $cache, int $ttl = 0): void
    {
        $this->validateKey($key); // Validate key

        // Is a instance?
        if (!is_object($object)) {
            throw new \UnexpectedValueException('Memory component may only store instances');
        }

        // Store in run-time memory
        $this->instances[$key] = $object;

        // Store in cache?
        if ($this->cache && $cache) {
            try {
                $this->cache->set($key, clone $object, $ttl);
            } catch (CacheException $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }
    }

    /**
     * @param string $key
     */
    private function validateKey(string $key)
    {
        if (!preg_match('/^[\w\-\.\@\+\:]{3,128}$/i', $key)) {
            throw new \InvalidArgumentException('Invalid memory object key');
        }
    }
}