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
     * @param $domain
     * @return bool
     */
    public static function isValidWebDomain($domain): bool
    {
        if (is_string($domain) && preg_match('/^([a-z0-9\-]+\.)?[a-z0-9\-]+(\.[a-z]{2,8})?\.[a-z]{2,8}$/i', $domain)) {
            return true;
        }

        return false;
    }

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

    /**
     * @param $timeStamp
     * @return bool
     */
    public static function isValidTimeStamp($timeStamp): bool
    {
        if (is_int($timeStamp) && $timeStamp > 0x3B9ACA00) {
            return true;
        }

        return false;
    }

    /**
     * @param $value
     * @param string|null $allow
     * @return bool
     */
    public static function isASCII($value, ?string $allow = null): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $allowed = $allow ? preg_quote($allow, "/") : "";
        $match = '/^[\w\s' . $allowed . ']*$/';

        return preg_match($match, $value) ? true : false;
    }

    /**
     * @param $value
     * @return bool
     */
    public static function isUTF8($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return strlen($value) !== mb_strlen($value) ? true : false;
    }
}