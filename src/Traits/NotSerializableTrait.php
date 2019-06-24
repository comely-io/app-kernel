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

namespace Comely\App\Traits;

/**
 * Trait NotSerializableTrait
 * @package Comely\App\Traits
 */
trait NotSerializableTrait
{
    final public function __sleep()
    {
        throw new \RuntimeException(get_called_class() . ' instance cannot be serialized');
    }

    final public function serialize()
    {
        throw new \RuntimeException(get_called_class() . ' instance cannot be serialized');
    }

    final public function __wakeup()
    {
        throw new \RuntimeException(get_called_class() . ' instance cannot be un-serialized');
    }

    final public function unserialize($serialized)
    {
        unset($serialized); // Just so my IDE stop giving unused param warning
        throw new \RuntimeException(get_called_class() . ' instance cannot be un-serialized');
    }
}