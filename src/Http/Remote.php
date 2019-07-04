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

use Comely\Http\Request;

/**
 * Class Remote
 * @package Comely\App\Http
 */
class Remote
{
    /** @var null|string */
    public $ipAddress;
    /** @var null|int */
    public $port;
    /** @var null|string */
    public $origin;
    /** @var null|string */
    public $agent;

    /**
     * Remote constructor.
     */
    public function __construct()
    {
        $this->set(null);
    }

    /**
     * @param string $method
     * @param $args
     */
    public function __call(string $method, $args)
    {
        switch ($method) {
            case "set":
                $this->set($args[0]);
                return;
        }

        throw new \DomainException('Cannot call inaccessible method');
    }

    /**
     * @param Request $req
     */
    private function set(?Request $req = null): void
    {
        $this->ipAddress = null;
        $this->origin = null;
        $this->agent = null;

        if ($req) {
            // CF IP Address
            if ($req->headers()->has("cf_connecting_ip")) {
                $this->ipAddress = $req->headers()->get("cf_connecting_ip");
            }

            // XFF
            if (!$this->ipAddress && $req->headers()->has("x_forwarded_for")) {
                $xff = explode(",", $req->headers()->get("x_forwarded_for"));
                $xff = preg_replace('/[^a-f0-9\.\:]/', '', strtolower($xff[0]));
                $this->ipAddress = trim($xff);
            }

            // Other Headers
            $this->origin = $req->headers()->get("referer");
            $this->agent = $req->headers()->get("user_agent");
        }

        // IP Address
        if (!$this->ipAddress) {
            $this->ipAddress = $_SERVER["REMOTE_ADDR"] ?? null;
        }

        // Port
        $this->port = $_SERVER["REMOTE_PORT"] ?? null;
        if (!is_null($this->port)) {
            $this->port = intval($this->port);
        }
    }
}