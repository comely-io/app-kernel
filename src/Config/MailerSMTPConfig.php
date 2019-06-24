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
use Comely\Utils\Validator\Exception\RangeException;
use Comely\Utils\Validator\Validator;

/**
 * Class MailerSMTPConfig
 * @package Comely\App\Config
 * @property-read string $host
 * @property-read int $port
 * @property-read int $timeOut
 * @property-read bool $tls
 * @property-read null|string $auth
 * @property-read null|string $username
 * @property-read null|string $password
 * @property-read null|string $serverName
 */
class MailerSMTPConfig implements ObjectMapperInterface
{
    /** @var string */
    protected $host;
    /** @var int */
    protected $port;
    /** @var int */
    protected $timeOut;
    /** @var bool */
    protected $tls;
    /** @var null|string */
    protected $auth;
    /** @var null|string */
    protected $username;
    /** @var null|string */
    protected $password;
    /** @var null|string */
    protected $serverName;

    /**
     * MailerSMTPConfig constructor.
     * @param array $smtp
     * @throws ObjectMapper\Exception\ObjectMapperException
     */
    public function __construct(array $smtp)
    {
        $objectMapper = new ObjectMapper($this);
        $objectMapper->map($smtp);
    }

    /**
     * @param string $prop
     * @return mixed
     */
    public function __get(string $prop)
    {
        switch ($prop) {
            case "host":
            case "port":
            case "timeOut":
            case "tls":
            case "auth":
            case "username":
            case "password":
            case "serverName":
                return $this->$prop;
        }

        throw new \DomainException('Cannot get value of inaccessible property');
    }

    /**
     * @param ObjectMapper $objectMapper
     */
    public function objectMapperProps(ObjectMapper $objectMapper): void
    {
        $objectMapper->prop("host")->dataTypes("string")->validate(function ($value) {
            return Validator::String($value)->validate(function (string $host) {
                $hostname = \Comely\App\Validator::isValidHostname($host);
                if (!$hostname) {
                    throw new ObjectMapper\Exception\ObjectMapperException('Mailer SMTP hostname is invalid');
                }

                return $hostname;
            });
        });

        $objectMapper->prop("port")->dataTypes("integer")->validate(function ($value) {
            try {
                return Validator::Integer($value)->range(1, 65535)->validate();
            } catch (RangeException $e) {
                throw new ObjectMapper\Exception\ObjectMapperException('SMTP port is out of range');
            }
        });

        $objectMapper->prop("timeOut")->dataTypes("integer")->validate(function ($value) {
            try {
                return Validator::Integer($value)->range(1, 30)->validate();
            } catch (RangeException $e) {
                throw new ObjectMapper\Exception\ObjectMapperException('SMTP timeout value is out of range');
            }
        });

        $objectMapper->prop("tls")->dataTypes("boolean");

        $objectMapper->prop("auth")->dataTypes("string")->nullable()->validate(function ($value) {
            return Validator::String($value)->lowerCase()->inArray(["plain", "login"])->validate();
        });

        $objectMapper->prop("username")->nullable()->dataTypes("string")->validate(function ($value) {
            try {
                return Validator::String($value)->len(3, 64)->validate();
            } catch (LengthException $e) {
                throw new ObjectMapper\Exception\ObjectMapperException('SMTP username length error');
            }
        });

        $objectMapper->prop("password")->nullable()->dataTypes("string")->validate(function ($value) {
            try {
                return Validator::String($value)->len(3, 64)->validate();
            } catch (LengthException $e) {
                throw new ObjectMapper\Exception\ObjectMapperException('SMTP password length error');
            }
        });

        $objectMapper->prop("serverName")->nullable()->dataTypes("string")->validate(function ($value) {
            return Validator::String($value)->lowerCase()->match('/^[a-z0-9\-]+(\.[a-z0-9\-]+)*$/')->validate();
        });
    }
}