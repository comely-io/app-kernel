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

/**
 * Class Validator
 * @package Comely\App
 */
class Validator
{
    /**
     * @param $hostname
     * @return string|null
     */
    public static function isValidHostname($hostname): ?string
    {
        if (!is_string($hostname) || !$hostname) {
            return null;
        }

        $hostname = strtolower($hostname);
        if (preg_match('/^[a-z0-9\-]+(\.[a-z0-9\-]+)*$/', $hostname)) {
            return $hostname; // Validated as Domain
        }

        $filterIP = filter_var($hostname, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6);
        return $filterIP ? $filterIP : null;
    }

    /**
     * @param $ip
     * @param bool $allowV6
     * @return bool
     */
    public static function isValidIP($ip, bool $allowV6 = true): bool
    {
        if (!is_string($ip) || !$ip) {
            return false;
        }

        $flags = FILTER_FLAG_IPV4;
        if ($allowV6) {
            $flags = FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6;
        }

        return filter_var($ip, FILTER_VALIDATE_IP, $flags);
    }
}