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

namespace Comely\App;

use Comely\App\ErrorHandler\ErrorLog;
use Comely\App\ErrorHandler\ErrorMsg;
use Comely\App\ErrorHandler\Screen;
use Comely\App\Traits\NoDumpTrait;
use Comely\App\Traits\NotCloneableTrait;
use Comely\App\Traits\NotSerializableTrait;

/**
 * Class ErrorHandler
 * @package Comely\App
 */
class ErrorHandler
{
    /** @var AppKernel */
    private $appKernel;
    /** @var ErrorLog */
    private $errors;
    /** @var int */
    private $pathOffset;

    use NoDumpTrait;
    use NotCloneableTrait;
    use NotSerializableTrait;

    /**
     * @param \Exception $e
     * @param int $type
     */
    public static function Exception2Error(\Exception $e, int $type = E_USER_WARNING): void
    {
        trigger_error(sprintf('[%s][#%s] %s', get_class($e), $e->getCode(), $e->getMessage()), $type);
    }

    /**
     * ErrorHandler constructor.
     * @param AppKernel $appKernel
     */
    public function __construct(AppKernel $appKernel)
    {
        $this->appKernel = $appKernel;
        $this->errors = new ErrorLog();
        $this->pathOffset = strlen($appKernel->dirs()->root()->path());

        set_error_handler([$this, "handleError"]);
        set_exception_handler([$this, "handleThrowable"]);
    }

    /**
     * @return ErrorLog
     */
    public function errors(): ErrorLog
    {
        return $this->errors;
    }

    /**
     * @param \Throwable $ex
     */
    public function handleThrowable(\Throwable $ex): void
    {
        $this->screen($ex);
    }

    /**
     * @param string $message
     * @param int $type
     */
    public function triggerIfDebug(string $message, int $type = E_USER_NOTICE): void
    {
        if (!in_array($type, [E_USER_NOTICE, E_USER_WARNING])) {
            throw new \InvalidArgumentException('Invalid triggered error type');
        }

        $debugBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $file = strval($debugBacktrace[0]["file"] ?? "");
        $line = intval($debugBacktrace[0]["line"] ?? -1);

        $error = new ErrorMsg();
        $error->type = $this->type($type);
        $error->message = $message;
        $error->file = $this->filePath($file);
        $error->line = $line;
        $error->triggered = $this->appKernel->dev() ? true : false;
        $this->errors->append($error);
    }

    /**
     * @param int $type
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     */
    public function handleError(int $type, string $message, string $file, int $line): bool
    {
        if (error_reporting() === 0) return false;

        // Check if error can be handled
        if (in_array($type, [2, 8, 512, 1024, 2048, 8192, 16384])) {
            $error = new ErrorMsg();
            $error->type = $this->type($type);
            $error->message = $message;
            $error->file = $this->filePath($file);
            $error->line = $line;
            $error->triggered = true;
            $this->errors->append($error);
        } else {
            // Proceed to shutdown screen
            try {
                throw new \RuntimeException($message, $type);
            } catch (\RuntimeException $e) {
                $this->screen($e);
            }
        }

        return true;
    }

    /**
     * @param \Throwable $ex
     */
    private function screen(\Throwable $ex): void
    {
        $siteTitle = null;
        if ($this->appKernel->isBootstrapped()) {
            $siteTitle = $this->appKernel->config()->site()->title;
        }

        $screen = new Screen($this->appKernel->dev(), $this->errors, $this->pathOffset, $siteTitle);
        $screen->send($ex);
    }

    /**
     * @param string $path
     * @return string
     */
    private function filePath(string $path): string
    {
        return trim(substr($path, $this->pathOffset), DIRECTORY_SEPARATOR);
    }

    /**
     * @param int $type
     * @return string
     */
    private function type(int $type): string
    {
        switch ($type) {
            case 1:
                return "Fatal Error";
            case 2:
                return "Warning";
            case 4:
                return "Parse Error";
            case 8:
                return "Notice";
            case 16:
                return "Core Error";
            case 32:
                return "Core Warning";
            case 64:
                return "Compile Error";
            case 128:
                return "Compile Warning";
            case 256:
                return "Error";
            case 512:
                return "Warning";
            case 1024:
                return "Notice";
            case 2048:
                return "Strict";
            case 4096:
                return "Recoverable";
            case 8192:
                return "Deprecated";
            case 16384:
                return "Deprecated";
            default:
                return "Unknown";
        }
    }

}