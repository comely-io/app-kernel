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
use Comely\Utils\Validator\Validator;

/**
 * Class SessionsServiceConfig
 * @package Comely\App\Config
 * @property-read string $cookie
 */
class SessionsServiceConfig implements ObjectMapperInterface
{
    /** @var string */
    protected $cookie;

    /**
     * SessionsServiceConfig constructor.
     * @param array $sessions
     * @throws ObjectMapper\Exception\ObjectMapperException
     */
    public function __construct(array $sessions)
    {
        $objectMapper = new ObjectMapper($this);
        $objectMapper->map($sessions);
    }

    /**
     * @param string $prop
     * @return mixed
     */
    public function __get(string $prop)
    {
        switch ($prop) {
            case "cookie":
                return $this->$prop;
        }

        throw new \DomainException('Cannot get value of inaccessible property');
    }

    /**
     * @param ObjectMapper $objectMapper
     */
    public function objectMapperProps(ObjectMapper $objectMapper): void
    {
        $objectMapper->prop("cookie")->dataTypes("string")->nullable()->validate(function ($cookie) {
            return Validator::String($cookie)->match('/^\w+$/')->validate();
        });
    }
}