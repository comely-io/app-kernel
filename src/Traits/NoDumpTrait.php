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
 * Trait NoDumpTrait
 * @package Comely\App\Traits
 */
trait NoDumpTrait
{
    /**
     * @return array
     */
    final public function __debugInfo()
    {
        return [get_called_class()];
    }
}