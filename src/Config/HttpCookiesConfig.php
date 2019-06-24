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

use Comely\Utils\OOP\ObjectMapper;
use Comely\Utils\OOP\ObjectMapper\ObjectMapperInterface;
use Comely\Utils\Time\TimeUnits;
use Comely\Utils\Validator\Exception\RangeException;
use Comely\Utils\Validator\Validator;

/**
 * Class HttpCookiesConfig
 * @package Comely\App\Config
 * @property-read int $expire
 * @property-read string $path
 * @property-read string $domain
 * @property-read bool $secure
 * @property-read bool $httpOnly
 */
class HttpCookiesConfig implements ObjectMapperInterface
{
    /** @var int */
    protected $expire;
    /** @var string */
    protected $path;
    /** @var string */
    protected $domain;
    /** @var bool */
    protected $secure;
    /** @var bool */
    protected $httpOnly;

    /**
     * HttpCookiesConfig constructor.
     * @param array $cookies
     * @throws ObjectMapper\Exception\ObjectMapperException
     */
    public function __construct(array $cookies)
    {
        $objectMapper = new ObjectMapper($this);
        $objectMapper->map($cookies);
    }

    /**
     * @param string $prop
     * @return mixed
     */
    public function __get(string $prop)
    {
        switch ($prop) {
            case "expire":
            case "path":
            case "domain":
            case "secure":
            case "httpOnly":
                return $this->$prop;
        }

        throw new \DomainException('Cannot get value of inaccessible property');
    }

    /**
     * @param ObjectMapper $objectMapper
     */
    public function objectMapperProps(ObjectMapper $objectMapper): void
    {
        $objectMapper->prop("expire")->dataTypes("integer")->validate(function ($expire) {
            if (is_string($expire)) {
                $expire = (new TimeUnits())->stringToTime($expire);
            }

            try {
                return Validator::Integer($expire)->range(3600, 2592000)->validate();
            } catch (RangeException $e) {
                throw new ObjectMapper\Exception\ObjectMapperException('Cookie expire value must be between 1h to 30d');
            }
        });

        $objectMapper->prop("path")->dataTypes("string")->validate(function ($path) {
            return Validator::String($path)->match('/^(\/[\w\-\.]*)+$/')->validate();
        });

        $objectMapper->prop("domain")->nullable()->dataTypes("string")->validate(function ($domain) {
            return Validator::String($domain)->lowerCase()->match('/^[a-z0-9\-]+(\.[a-z0-9\-]+)*$/')->validate();
        });

        $objectMapper->prop("secure")->dataTypes("boolean");
        $objectMapper->prop("httpOnly")->dataTypes("boolean");
    }
}