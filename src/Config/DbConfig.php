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
use Comely\Utils\Validator\Exception\LengthException;
use Comely\Utils\Validator\Exception\NotInArrayException;
use Comely\Utils\Validator\Validator;

/**
 * Class DbConfig
 * @package Comely\App\Config
 */
class DbConfig implements ObjectMapperInterface
{
    /** @var string */
    public $driver;
    /** @var string */
    public $host;
    /** @var string */
    public $name;
    /** @var string */
    public $username;
    /** @var null|string */
    public $password;

    /**
     * DbConfig constructor.
     * @param array $db
     * @throws ObjectMapper\Exception\ObjectMapperException
     */
    public function __construct(array $db)
    {
        $objectMapper = new ObjectMapper($this);
        $objectMapper->map($db);
    }

    /**
     * @param ObjectMapper $objectMapper
     */
    public function objectMapperProps(ObjectMapper $objectMapper): void
    {
        $objectMapper->prop("driver")->dataTypes("string")->validate(function ($value) {
            try {
                return Validator::String($value)->lowerCase()->inArray(["mysql", "pgsql", "sqlite"])->validate();
            } catch (NotInArrayException $e) {
                throw new ObjectMapper\Exception\ObjectMapperException('Database driver is invalid or not supported');
            }
        });

        $objectMapper->prop("host")->dataTypes("string")->validate(function ($value) {
            return Validator::String($value)->validate(function (string $host) {
                $hostname = \Comely\App\Validator::isValidHostname($host);
                if (!$hostname) {
                    throw new ObjectMapper\Exception\ObjectMapperException('Database hostname is invalid');
                }

                return $hostname;
            });
        });

        $objectMapper->prop("name")->dataTypes("string")->validate(function ($value) {
            return Validator::String($value)->match('/^\w+$/')->validate();
        });

        $objectMapper->prop("username")->dataTypes("string")->validate(function ($value) {
            return Validator::String($value)->match('/^\w+$/')->validate();
        });

        $objectMapper->prop("password")->dataTypes("string")->nullable()->validate(function ($value) {
            try {
                return Validator::String($value)->len(6, 64)->validate();
            } catch (LengthException $e) {
                throw new ObjectMapper\Exception\ObjectMapperException('Database password must be NULL or between 6-64 bytes');
            }
        });
    }
}