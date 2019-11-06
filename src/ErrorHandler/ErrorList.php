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

use Comely\App\Traits\NoDumpTrait;
use Comely\App\Traits\NotCloneableTrait;
use Comely\App\Traits\NotSerializableTrait;

/***
 * Class ErrorList
 * @package Comely\App\ErrorHandler
 */
class ErrorList implements \Iterator, \Countable
{
    /** @var array */
    private $errors;
    /** @var int */
    private $index;
    /** @var int */
    private $count;

    use NotCloneableTrait;
    use NoDumpTrait;
    use NotSerializableTrait;

    /**
     * ErrorList constructor.
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
        $this->errors = [];
        $this->index = 0;
        $this->count = 0;
    }

    /**
     * @param ErrorMsg $errorMsg
     */
    public function append(ErrorMsg $errorMsg): void
    {
        $this->errors[] = $errorMsg;
        $this->count++;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->errors;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * @return ErrorMsg
     */
    public function current(): ErrorMsg
    {
        return $this->errors[$this->index];
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->index;
    }

    /**
     * @return void
     */
    public function next(): void
    {
        ++$this->index;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->errors[$this->index]);
    }
}