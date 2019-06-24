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

use Comely\App\Exception\AppConfigException;
use Comely\DataTypes\Buffer\Binary;

/**
 * Class CipherConfig
 * @package Comely\App\Config
 */
class CipherConfig
{
    /** @var array */
    private $keys;

    /**
     * CipherConfig constructor.
     * @param array $keys
     * @throws AppConfigException
     */
    public function __construct(array $keys)
    {
        $this->keys = [];
        $pos = 0;
        foreach ($keys as $key => $entropy) {
            $pos++;
            if (!preg_match('/^\w{2,16}$/', $key)) {
                throw new AppConfigException(sprintf('Invalid label for cipher key at position # %d', $pos));
            }

            if (!is_string($entropy)) {
                throw new AppConfigException(
                    sprintf('Cipher key for "%s" must be of type string, got "%s"', $key, gettype($entropy))
                );
            }

            if (!preg_match('/^[a-f0-9]{64}$/i', $entropy)) {
                $entropy = hash("sha256", $entropy, false);
            }

            $this->keys[strtolower($key)] = hex2bin($entropy);
        }
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return ["App Cipher Keys"];
    }

    /**
     * @param string $key
     * @return Binary|null
     */
    public function get(string $key): ?Binary
    {
        $key = strtolower($key);
        if (isset($this->keys[$key])) {
            return new Binary($this->keys[$key]);
        }

        return null;
    }
}