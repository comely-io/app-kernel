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
use Comely\App\Exception\XSRF_Exception;
use Comely\App\Http\Response\Messages;
use Comely\App\Http\Security\XSRF;
use Comely\Http\Exception\ServiceNotConfiguredException;
use Comely\Knit\Exception\KnitException;
use Comely\Knit\Knit;
use Comely\Knit\Template;
use Comely\Sessions\ComelySession;
use Comely\Sessions\Exception\SessionsException;
use Comely\Utils\OOP\OOP;

/**
 * Class GenericHttpController
 * @package Comely\App\Http\Controllers
 */
abstract class GenericHttpController extends AbstractAppController
{
    /** @var ComelySession */
    private $session;
    /** @var Messages */
    private $messages;
    /** @var XSRF */
    private $xsrf;
    /** @var Messages */
    private $flashMessages;

    /**
     * @throws \Exception
     */
    public function callback(): void
    {
        parent::callback();
        $this->messages = new Messages();

        $this->response()->header("content-type", "application/json");

        $this->response()->set("status", false);
        $this->response()->set("messages", null);

        // Controller method
        $httpRequestMethod = strtolower($this->request()->method());
        $controllerMethod = $httpRequestMethod;

        // Explicit method name
        $queryStringMethod = explode("&", $this->request()->url()->query() ?? "")[0];
        if (preg_match('/^\w+$/', $queryStringMethod)) {
            $controllerMethod .= OOP::PascalCase($queryStringMethod);
            // If HTTP request method is GET, and assumed method doesn't exist, default controller is "get()"
            if ($httpRequestMethod === "get" && !method_exists($this, $controllerMethod)) {
                $controllerMethod = "get";
            }
        }

        // Execute
        try {
            if (!method_exists($this, $controllerMethod)) {
                throw new AppControllerException(
                    sprintf(
                        'Requested method "%s" not found in HTTP controller "%s" class',
                        $controllerMethod,
                        get_called_class()
                    )
                );
            }

            $this->onLoad(); // Event callback: onLoad
            call_user_func([$this, $controllerMethod]);
        } catch (\Exception $e) {
            if (preg_match('/html/', $this->response()->contentType() ?? "")) {
                throw $e; // Throw caught exception so it may be picked by Exception Handler (screen)
            }

            $param = $e instanceof AppControllerException ? $e->getParam() : null;
            $this->messages->danger($e->getMessage(), $param);
            if ($this->app->dev()) {
                $this->response()->set("caught", get_class($e));
                $this->response()->set("file", $e->getFile());
                $this->response()->set("line", $e->getLine());
                $this->response()->set("trace", $this->getExceptionTrace($e));
            }
        }

        $this->response()->set("messages", $this->messages->array()); // Messages
        if ($this->app->dev()) {
            $this->response()->set("errors", $this->app->errorHandler()->errors()); // Errors
        }

        // Set flash messages in session
        if ($this->flashMessages && $this->session) {
            $this->session->flash()->bags()->set("messages", serialize($this->flashMessages));
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

    /**
     * @param string|null $id
     * @param bool $setCookie
     * @throws ServiceNotConfiguredException
     * @throws \Comely\App\Exception\AppDirectoryException
     * @throws \Comely\Sessions\Exception\ComelySessionException
     * @throws \Comely\Sessions\Exception\StorageException
     */
    protected function initSession(?string $id = null, bool $setCookie = true): void
    {
        if ($this->session) {
            throw new \RuntimeException('Session is already instantiated');
        }

        $sessions = $this->app->services()->sessions();
        $sessionsConfig = $this->app->config()->services()->sessions();

        $sessionId = $id ?? null;
        if (!$sessionId) {
            $cookieName = $sessionsConfig->cookie;
            if (!$cookieName) {
                throw new ServiceNotConfiguredException('Sessions cookie name not configured');
            }

            $sessionId = $_COOKIE[$cookieName] ?? null;
        }

        if ($sessionId) {
            try {
                $this->session = $sessions->resume($sessionId);
            } catch (SessionsException $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }

        if (!$this->session) {
            $this->session = $sessions->start();
        }

        if ($setCookie && isset($cookieName)) {
            $this->app->http()->cookies()->set($cookieName, $this->session->id());
        }
    }

    /**
     * @return Messages
     */
    public function messages(): Messages
    {
        return $this->messages;
    }

    /**
     * @return Messages
     */
    public function flashMessages(): Messages
    {
        if (!$this->flashMessages) {
            if (!$this->session) {
                throw new \RuntimeException('Flash messages requires session instantiated');
            }

            $this->flashMessages = new Messages();
        }

        return $this->flashMessages;
    }

    /**
     * @return ComelySession
     */
    public function session(): ComelySession
    {
        if (!$this->session) {
            throw new \RuntimeException('Session was not instantiated');
        }

        return $this->session;
    }

    /**
     * @return XSRF
     * @throws XSRF_Exception
     */
    public function xsrf(): XSRF
    {
        if (!$this->xsrf) {
            if (!$this->session) {
                throw new XSRF_Exception('XSRF requires session instantiated');
            }

            $this->xsrf = new XSRF($this->app, $this->session);
        }

        return $this->xsrf;
    }

    /**
     * @return Knit
     * @throws \Comely\App\Exception\AppDirectoryException
     */
    public function knit(): Knit
    {
        return $this->app->services()->knit();
    }

    /**
     * @param string $templateFile
     * @return Template
     * @throws \Comely\App\Exception\AppDirectoryException
     * @throws \Comely\Knit\Exception\KnitException
     */
    public function template(string $templateFile): Template
    {
        return $this->knit()->template($templateFile);
    }

    /**
     * @param Template $template
     * @throws KnitException
     */
    public function body(Template $template): void
    {
        try {
            // Flash messages
            $flashMessages = null;
            if ($this->session) {
                $serializedFlash = $this->session->flash()->last()->get("messages");
                if ($serializedFlash) {
                    $flashMessages = unserialize($serializedFlash, [
                        "allowed_classes" => [
                            'Comely\App\Http\Response\Messages'
                        ]
                    ]);

                    if (!$flashMessages instanceof Messages) {
                        trigger_error('Failed to unserialize flash messages', E_USER_WARNING);
                    }

                    $flashMessages = $flashMessages->array();
                }
            }

            $config = [
                "site" => $this->app->config()->site()
            ];

            $template->assign("flashMessages", $flashMessages);
            $template->assign("errors", $this->app->errorHandler()->errors());
            $template->assign("config", $config);
            $template->assign("remote", $this->remote());

            // Default response type (despite of ACCEPT header)
            $this->response()->header("content-type", "text/html");

            // Populate Response "body" param
            $this->response()->body($template->knit());
        } catch (KnitException $e) {
            throw $e;
        }
    }
}