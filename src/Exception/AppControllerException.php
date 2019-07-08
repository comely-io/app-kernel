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
 * Class AppControllerException
 * @package Comely\App\Exception
 */
class AppControllerException extends AppException
{
    /** @var null|string */
    protected $param;

    /**
     * @param string $param
     * @return AppControllerException
     */
    public function setParam(string $param): self
    {
        $this->param = $param;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getParam(): ?string
    {
        return $this->param;
    }
}