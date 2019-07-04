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

namespace Comely\App\Exception;

/**
 * Class XSRF_Exception
 * @package Comely\App\Exception
 */
class XSRF_Exception extends AppControllerException
{
    public const TOKEN_MISMATCH = 0x0a;
    public const TOKEN_EXPIRED = 0x14;
    public const TOKEN_IP_MISMATCH = 0x1e;
    public const TOKEN_NOT_SET = 0x28;
}