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

namespace Comely\App\Http\Response;

/**
 * Class Messages
 * @package Comely\App\Http\Response
 */
class Messages
{
    /** @var array */
    private $messages;

    /**
     * Messages constructor.
     */
    public function __construct()
    {
        $this->messages = [];
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->messages;
    }

    /**
     * @param string $type
     * @param string $message
     * @param string|null $param
     * @return Messages
     */
    public function append(string $type, string $message, ?string $param = null): self
    {
        $this->messages[] = [
            "type" => $type,
            "message" => $message,
            "param" => $param
        ];

        return $this;
    }

    /**
     * @param string $message
     * @param string|null $param
     * @return Messages
     */
    public function info(string $message, ?string $param = null): self
    {
        return $this->append("info", $message, $param);
    }

    /**
     * @param string $message
     * @param string|null $param
     * @return Messages
     */
    public function warning(string $message, ?string $param = null): self
    {
        return $this->append("warning", $message, $param);
    }

    /**
     * @param string $message
     * @param string|null $param
     * @return Messages
     */
    public function danger(string $message, ?string $param = null): self
    {
        return $this->append("danger", $message, $param);
    }

    /**
     * @param string $message
     * @param string|null $param
     * @return Messages
     */
    public function success(string $message, ?string $param = null): self
    {
        return $this->append("success", $message, $param);
    }

    /**
     * @param string $message
     * @param string|null $param
     * @return Messages
     */
    public function primary(string $message, ?string $param = null): self
    {
        return $this->append("primary", $message, $param);
    }

    /**
     * @param string $message
     * @param string|null $param
     * @return Messages
     */
    public function secondary(string $message, ?string $param = null): self
    {
        return $this->append("secondary", $message, $param);
    }
}