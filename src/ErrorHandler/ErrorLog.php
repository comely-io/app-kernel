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
 * Class ErrorLog
 * @package Comely\App\ErrorHandler
 */
class ErrorLog implements \Countable
{
    /** @var ErrorList */
    private $triggered;
    /** @var ErrorList */
    private $logged;

    /**
     * ErrorLog constructor.
     */
    public function __construct()
    {
        $this->flush();
    }

    /**
     * @return void
     */
    public function flush(): void
    {
        $this->triggered = new ErrorList();
        $this->logged = new ErrorList();
    }

    /**
     * @param ErrorMsg $error
     */
    public function append(ErrorMsg $error): void
    {
        if (!is_bool($error->triggered)) {
            throw new \InvalidArgumentException('ErrorMsg object prop "triggered" must be of type boolean');
        }

        if ($error->triggered) {
            $this->triggered->append($error);
        } else {
            $this->logged->append($error);
        }
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->triggered->count() + $this->logged->count();
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return array_merge($this->triggered->array(), $this->logged->array());
    }

    /**
     * @return ErrorList
     */
    public function triggered(): ErrorList
    {
        return $this->triggered;
    }

    /**
     * @return ErrorList
     */
    public function logged(): ErrorList
    {
        return $this->logged;
    }
}