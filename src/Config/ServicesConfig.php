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

namespace Comely\App\Config;

use Comely\App\Exception\AppConfigException;
use Comely\Utils\OOP\ObjectMapper\Exception\ObjectMapperException;

/**
 * Class ServicesConfig
 * @package Comely\App\Config
 */
class ServicesConfig
{
    /** @var CacheServiceConfig|null */
    private $cache;

    /**
     * ServicesConfig constructor.
     * @param array $services
     * @throws AppConfigException
     */
    public function __construct(array $services)
    {
        $cacheConfig = $services["cache"] ?? null;
        if (is_array($cacheConfig)) {
            try {
                $this->cache = new CacheServiceConfig($cacheConfig);
            } catch (ObjectMapperException $e) {
                throw new AppConfigException($e->getMessage());
            }
        }
    }

    /**
     * @return CacheServiceConfig|null
     */
    public function cache(): ?CacheServiceConfig
    {
        return $this->cache;
    }
}