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
 * Class SiteConfig
 * @package Comely\App\Config
 * @property-read string $title
 * @property-read string $domain
 * @property-read bool $https
 * @property-read string $url
 */
class SiteConfig implements ObjectMapperInterface
{
    /** @var string */
    protected $title;
    /** @var string */
    protected $domain;
    /** @var bool */
    protected $https;
    /** @var string */
    protected $url;

    /**
     * SiteConfig constructor.
     * @param array $site
     * @throws ObjectMapper\Exception\ObjectMapperException
     */
    public function __construct(array $site)
    {
        $objectMapper = new ObjectMapper($this);
        $objectMapper->map($site);

        $protocol = $this->https ? "https" : "http";
        $this->url = sprintf('%s://%s/', $protocol, $this->domain);
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return [
            "title" => $this->title,
            "domain" => $this->domain,
            "https" => $this->https,
            "url" => $this->url
        ];
    }

    /**
     * @param string $prop
     * @return mixed
     */
    public function __get(string $prop)
    {
        switch ($prop) {
            case "title":
            case "domain":
            case "https":
            case "url":
                return $this->$prop;
        }

        throw new \DomainException('Cannot get value of inaccessible property');
    }

    /**
     * @param ObjectMapper $objectMapper
     */
    public function objectMapperProps(ObjectMapper $objectMapper): void
    {
        $objectMapper->prop("title")->dataTypes("string")->validate(function ($value) {
            return Validator::String($value)->match('/^[\w\.\-\@\!\~]+(\s+[\w\.\-\@\!\~]+)*$/')->validate();
        });

        $objectMapper->prop("domain")->dataTypes("string")->validate(function ($value) {
            if (is_string($value)) {
                $value = strtolower($value);
                if (substr($value, 0, 4) === "www.") {
                    $value = substr($value, 4);
                }
            }

            return Validator::String($value)->match('/^[a-z0-9\-]+(\.[a-z0-9\-]+)*$/')->validate();
        });

        $objectMapper->prop("https")->dataTypes("boolean");
    }
}