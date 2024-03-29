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
use Comely\Utils\OOP\OOP;

/**
 * Class API_Controller
 * @package Comely\App\Http\Controllers
 */
abstract class API_Controller extends AbstractAppController
{
    protected const EXPLICIT_METHOD_NAMES = false;

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

        // Controller method
        $httpRequestMethod = strtolower($this->request()->method());
        $controllerMethod = $httpRequestMethod;

        // Explicit method name
        if (static::EXPLICIT_METHOD_NAMES) {
            $queryStringMethod = explode("&", $this->request()->url()->query() ?? "")[0];
            if (preg_match('/^\w+$/', $queryStringMethod)) {
                $controllerMethod .= OOP::PascalCase($queryStringMethod);
            }
        }

        // Execute
        try {
            if (!method_exists($this, $controllerMethod)) {
                if ($httpRequestMethod === "options") {
                    $this->response()->set("status", true);
                    $this->response()->set("options", []);
                    return;
                } else {
                    throw new AppControllerException(
                        sprintf('Endpoint "%s" does not support "%s" method', get_called_class(), strtoupper($controllerMethod))
                    );
                }
            }

            $this->onLoad(); // Event callback: onLoad
            call_user_func([$this, $controllerMethod]);
        } catch (\Exception $e) {
            $this->response()->set("status", false);
            $this->response()->set("error", $e->getMessage());

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

        $displayErrors = $this->app->dev() ?
            $this->app->errorHandler()->errors()->all() :
            $this->app->errorHandler()->errors()->triggered()->array();

        if ($displayErrors) {
            $this->response()->set("errors", $displayErrors); // Errors
        }

        $this->onFinish(); // Event callback: onFinish
    }

    final public function status(bool $status): self
    {
        $this->response()->set("status", $status);
        return $this;
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