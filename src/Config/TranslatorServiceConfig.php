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

declare(strict_typest=1);

namespace Comely\App\Config;

use Comely\Utils\OOP\ObjectMapper;
use Comely\Utils\Validator\Validator;

/**
 * Class TranslatorServiceConfig
 * @package Comely\App\Config
 * @property-read null|string $cookie
 * @property-read null|string $fallback
 * @property-read bool $caching
 */
class TranslatorServiceConfig implements ObjectMapper\ObjectMapperInterface
{
    /** @var null|string */
    protected $cookie;
    /** @var null|string */
    protected $fallback;
    /** @var bool */
    protected $caching;

    /**
     * TranslatorServiceConfig constructor.
     * @param array $translator
     * @throws ObjectMapper\Exception\ObjectMapperException
     */
    public function __construct(array $translator)
    {
        $objectMapper = new ObjectMapper($this);
        $objectMapper->map($translator);
    }

    /**
     * @param string $prop
     * @return mixed
     */
    public function __get(string $prop)
    {
        switch ($prop) {
            case "cookie":
            case "fallback":
            case "caching":
                return $this->$prop;
        }

        throw new \DomainException('Cannot get value of inaccessible property');
    }

    /**
     * @param ObjectMapper $objectMapper
     */
    public function objectMapperProps(ObjectMapper $objectMapper): void
    {
        $objectMapper->prop("cookie")->nullable()->dataTypes("string")->validate(function ($value) {
            return Validator::String($value)->match('/^\w+$/')->validate();
        });

        $objectMapper->prop("fallback")->nullable()->dataTypes("string")->validate(function ($value) {
            return Validator::String($value)->match('/^[\w\-]+$/')->validate();
        });

        $objectMapper->prop("caching")->dataTypes("boolean");
    }
}