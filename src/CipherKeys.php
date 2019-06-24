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

namespace Comely\App;

use Comely\App\Exception\AppConfigException;
use Comely\App\Traits\NotCloneableTrait;
use Comely\App\Traits\NotSerializableTrait;
use Comely\Utils\Security\Cipher;

/**
 * Class CipherKeys
 * @package Comely\App
 */
class CipherKeys
{
    /** @var AppKernel */
    private $appKernel;
    /** @var array */
    private $keys;

    use NotCloneableTrait;
    use NotSerializableTrait;

    /**
     * CipherKeys constructor.
     * @param AppKernel $appKernel
     */
    public function __construct(AppKernel $appKernel)
    {
        $this->appKernel = $appKernel;
        $this->keys = [];
    }

    /**
     * @param string $key
     * @return Cipher
     * @throws AppConfigException
     * @throws \Comely\Utils\Security\Exception\CipherException
     */
    public function get(string $key): Cipher
    {
        if (!preg_match('/^\w{2,16}$/', $key)) {
            throw new \InvalidArgumentException('Invalid cipher key tag');
        }

        $key = strtolower($key);
        if (array_key_exists($key, $this->keys)) {
            return $this->keys[$key];
        }

        $cipherConfig = $this->appKernel->config()->services()->cipher();
        if (!$cipherConfig) {
            throw new AppConfigException('Cipher service is not configured');
        }

        $entropy = $cipherConfig->get($key);
        if (!$entropy) {
            throw new AppConfigException(sprintf('Cipher key "%s" does not exist', $key));
        }

        $defaultEntropy = hash("sha256", "enter some random words here", false);
        if (hash_equals($defaultEntropy, $entropy->base16()->hexits())) {
            throw new AppConfigException(
                sprintf('Cipher key "%s" is set to default value; Please change it first', $key)
            );
        }

        $cipher = new Cipher($entropy);
        $this->keys[$key] = $cipher;
        return $cipher;
    }
}