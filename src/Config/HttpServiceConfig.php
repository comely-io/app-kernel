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
 * Class HttpServiceConfig
 * @package Comely\App\Config
 */
class HttpServiceConfig
{
    /** @var HttpCookiesConfig */
    private $cookies;

    /**
     * HttpServiceConfig constructor.
     * @param array $http
     * @throws AppConfigException
     */
    public function __construct(array $http)
    {
        $cookiesConfig = $http["cookies"] ?? null;
        if (is_array($cookiesConfig)) {
            try {
                $this->cookies = new HttpCookiesConfig($cookiesConfig);
            } catch (ObjectMapperException $e) {
                throw new AppConfigException($e->getMessage());
            }
        }
    }

    /**
     * @return HttpCookiesConfig|null
     */
    public function cookies(): ?HttpCookiesConfig
    {
        return $this->cookies;
    }
}