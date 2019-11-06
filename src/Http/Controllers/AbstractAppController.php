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

namespace Comely\App\Http\Controllers;

use Comely\App\AppKernel;
use Comely\App\ErrorHandler;
use Comely\App\Http\Remote;
use Comely\Http\Router\AbstractController;

/**
 * Class AppController
 * @package Comely\App\Http\Controllers
 */
abstract class AbstractAppController extends AbstractController
{
    /** @var AppKernel */
    protected $app;

    /**
     * @return ErrorHandler
     */
    public function errorHandler(): ErrorHandler
    {
        return $this->app->errorHandler();
    }

    /**
     * @return void
     */
    public function callback(): void
    {
        $this->app = AppKernel::getInstance();
        call_user_func([$this->app->http()->remote(), "set"], $this->request()); // Register REMOTE_* values
    }

    /**
     * @param \Exception $e
     * @return array
     */
    protected function getExceptionTrace(\Exception $e): array
    {
        return array_map(function (array $trace) {
            unset($trace["args"]);
            return $trace;
        }, $e->getTrace());
    }

    /**
     * @return Remote
     */
    protected function remote(): Remote
    {
        return $this->app->http()->remote();
    }
}