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
use Comely\Utils\Validator\Exception\NotInArrayException;
use Comely\Utils\Validator\Exception\RangeException;
use Comely\Utils\Validator\Validator;

/**
 * Class CacheServiceConfig
 * @package Comely\App\Config
 * @property-read string $engine
 * @property-read string $host
 * @property-read int $port
 * @property-read null|int $timeOut
 */
class CacheServiceConfig implements ObjectMapperInterface
{
    /** @var string */
    protected $engine;
    /** @var string */
    protected $host;
    /** @var int */
    protected $port;
    /** @var null|int */
    protected $timeOut;

    /**
     * CacheServiceConfig constructor.
     * @param array $config
     * @throws ObjectMapper\Exception\ObjectMapperException
     */
    public function __construct(array $config)
    {
        $objectMapper = new ObjectMapper($this);
        $objectMapper->map($config);
    }

    /**
     * @param string $prop
     * @return mixed
     */
    public function __get(string $prop)
    {
        switch ($prop) {
            case "engine":
            case "host":
            case "port":
            case "timeOut":
                return $this->$prop;
        }

        throw new \DomainException('Cannot get value of inaccessible property');
    }

    /**
     * @param ObjectMapper $objectMapper
     */
    public function objectMapperProps(ObjectMapper $objectMapper): void
    {
        $objectMapper->prop("engine")->dataTypes("string")->validate(function ($engine) {
            try {
                return Validator::String($engine)->lowerCase()->inArray(["redis", "memcached"])->validate();
            } catch (NotInArrayException $e) {
                throw new ObjectMapper\Exception\ObjectMapperException('Invalid cache engine/store');
            }
        });

        $objectMapper->prop("host")->dataTypes("string")->validate(function ($value) {
            return Validator::String($value)->validate(function (string $host) {
                $hostname = \Comely\App\Validator::isValidHostname($host);
                if (!$hostname) {
                    throw new ObjectMapper\Exception\ObjectMapperException('Cache hostname is invalid');
                }

                return $hostname;
            });
        });

        $objectMapper->prop("port")->dataTypes("integer")->validate(function ($port) {
            try {
                return Validator::Integer($port)->range(1024, 65535)->validate();
            } catch (RangeException $e) {
                throw new ObjectMapper\Exception\ObjectMapperException('Port must be between 1024-65535');
            }
        });

        $objectMapper->prop("timeOut")->nullable()->dataTypes("integer")->validate(function ($timeOut) {
            try {
                return Validator::Integer($timeOut)->range(1, 30)->validate();
            } catch (RangeException $e) {
                throw new ObjectMapper\Exception\ObjectMapperException('Timeout value must be between 1-30 seconds');
            }
        });
    }
}