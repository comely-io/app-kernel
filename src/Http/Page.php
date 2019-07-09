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

namespace Comely\App\Http;

use Comely\App\Http\Controllers\GenericHttpController;

/**
 * Class Page
 * @package Comely\App\Http
 */
class Page
{
    /** @var array */
    private $props;
    /** @var array */
    private $assets;

    /**
     * Page constructor.
     * @param GenericHttpController $controller
     * @throws \Comely\App\Exception\XSRF_Exception
     * @throws \Comely\Utils\Security\Exception\PRNG_Exception
     */
    public function __construct(GenericHttpController $controller)
    {
        $this->assets = [];
        $this->props = [
            "title" => null,
            "language" => null,
            "index" => $this->index(0),
            "root" => $controller->request()->url()->root(),
            "token" => $controller->xsrf()->token() ?? $controller->xsrf()->generate()
        ];
    }

    /**
     * @param string $prop
     * @param $value
     * @return Page
     */
    public function prop(string $prop, $value): self
    {
        if (!preg_match('/^[\w\.]{3,32}$/', $prop)) {
            throw new \InvalidArgumentException('Invalid page property name');
        }

        if (in_array(strtolower($prop), ["title", "index", "root", "token", "assets"])) {
            throw new \OutOfBoundsException(sprintf('Cannot override "%s" page property', $prop));
        }

        $valueType = gettype($value);
        switch ($valueType) {
            case "string":
            case "integer":
            case "boolean":
            case "double":
            case "NULL":
                $this->props[$prop] = $value;
                break;
            default:
                throw new \UnexpectedValueException(
                    sprintf('Value of type "%s" cannot be stored as page prop', $valueType)
                );
        }

        return $this;
    }

    /**
     * @param string $title
     * @return Page
     */
    public function title(string $title): self
    {
        $this->props["title"] = $title;
        return $this;
    }

    /**
     * @param int $a
     * @param int $b
     * @param int $c
     * @return Page
     */
    public function index(int $a, int $b = 0, int $c = 0): self
    {
        $this->props["index"] = ["a" => $a, "b" => $b, "c" => $c];
        return $this;
    }

    /**
     * @param string $uri
     * @return Page
     */
    public function css(string $uri): self
    {
        $this->assets[] = [
            "type" => "css",
            "uri" => $uri
        ];

        return $this;
    }

    /**
     * @param string $uri
     * @return Page
     */
    public function js(string $uri): self
    {
        $this->assets[] = [
            "type" => "js",
            "uri" => $uri
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return array_merge($this->props, ["assets" => $this->assets]);
    }
}