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

namespace Comely\App\ErrorHandler;

/**
 * Class ErrorMsg
 * @package Comely\App\ErrorHandler
 */
class ErrorMsg
{
    /** @var int */
    public $type;
    /** @var string */
    public $message;
    /** @var string */
    public $file;
    /** @var int */
    public $line;
    /** @var bool */
    public $triggered;
    /** @var float */
    public $timeStamp;

    /**
     * ErrorMsg constructor.
     */
    public function __construct()
    {
        $this->triggered = false;
        $this->timeStamp = microtime(true);
    }
}