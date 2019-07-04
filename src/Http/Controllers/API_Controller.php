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

use Comely\App\Exception\AppControllerException;

/**
 * Class API_Controller
 * @package Comely\App\Http\Controllers
 */
abstract class API_Controller extends AbstractAppController
{
    /**
     * @return void
     */
    final public function callback(): void
    {
        parent::callback(); // Set AppKernel instance

        // Default response type (despite of ACCEPT header)
        $this->response()->header("content-type", "application/json");

        // Prepare response
        $this->response()->set("status", false);
        $this->response()->set("message", null);

        // Controller method
        $controllerMethod = strtolower($this->request()->method());

        // Execute
        try {
            if (!method_exists($this, $controllerMethod)) {
                throw new AppControllerException(
                    sprintf('Endpoint "%s" does not support "%s" method', get_called_class(), strtoupper($controllerMethod))
                );
            }

            $this->onLoad(); // Event callback: onLoad
            call_user_func([$this, $controllerMethod]);
        } catch (\Exception $e) {
            $this->response()->set("message", $e->getMessage());

            if ($e instanceof AppControllerException) {
                $param = $e->getParam();
                if ($param) {
                    $this->response()->set("param", $param);
                }
            }

            if ($this->app->dev()) {
                $this->response()->set("caught", get_class($e));
                $this->response()->set("file", $e->getFile());
                $this->response()->set("line", $e->getLine());
                $this->response()->set("trace", $this->getExceptionTrace($e));
            }
        }

        if ($this->app->dev()) {
            $this->response()->set("errors", $this->app->errorHandler()->errors()); // Errors
        }

        $this->onFinish(); // Event callback: onFinish
    }

    /**
     * @return void
     */
    abstract public function onLoad(): void;

    /**
     * @return void
     */
    abstract public function onFinish(): void;
}