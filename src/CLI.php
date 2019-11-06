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

use Comely\CLI\Abstract_CLI_Script;
use Comely\CLI\ASCII\Banners;
use Comely\Filesystem\Directory;
use Comely\Utils\OOP\OOP;

/**
 * Class CLI
 * @package Comely\App
 */
class CLI extends \Comely\CLI\CLI
{
    /** @var AppKernel */
    private $app;

    /**
     * CLI constructor.
     * @param string $appKernelClass
     * @param string $rootPath
     * @param Directory $bin
     * @param array $args
     */
    public function __construct(string $appKernelClass, string $rootPath, Directory $bin, array $args)
    {
        try {
            parent::__construct($bin, $args);

            // Bootstrap App Kernel
            if (!class_exists($appKernelClass)) {
                throw new \UnexpectedValueException('AppKernel class does not exist');
            } elseif (!is_a($appKernelClass, 'Comely\App\AppKernel', true)) {
                throw new \UnexpectedValueException('AppKernel class is not an instance of "Comely\App\AppKernel"');
            }

            // Bootstrapper Flags
            $env = $this->flags()->get("env") ?? "cli";
            if (!is_string($env) || !preg_match('/\w+/', $env)) {
                throw new \UnexpectedValueException('Invalid "env" flag');
            }

            $bootstrapper = (new Bootstrapper($rootPath))
                ->env($env)
                ->dev($this->flags()->has("dev"))
                ->loadCachedConfig($this->flags()->has("noCache") ? false : true);

            $appKernel = call_user_func(sprintf('%s::Bootstrap', $appKernelClass), $bootstrapper);
            $this->app = $appKernel;
        } catch (\Throwable $t) {
            $this->exception2Str($t);
            $this->finish();
            exit();
        }

        // Events
        $this->events()->scriptNotFound()->listen(function (\Comely\CLI\CLI $cli, string $scriptClassName) {
            $this->printAppHeader();
            $cli->print(sprintf("CLI script {red}{invert} %s {/} not found", OOP::baseClassName($scriptClassName)));
            $cli->print("");
        });

        $this->events()->scriptLoaded()->listen(function (\Comely\CLI\CLI $cli, Abstract_CLI_Script $script) {
            $displayHeader = @constant($this->execClassName . "::DISPLAY_HEADER") ?? true;
            if ($displayHeader) {
                $this->printAppHeader();
            }

            $displayLoadedName = @constant($this->execClassName . "::DISPLAY_LOADED_NAME") ?? true;
            if ($displayLoadedName) {
                $cli->inline(sprintf('CLI script {green}{invert} %s {/} loaded', OOP::baseClassName(get_class($script))));
                $cli->repeat(".", 3, 100, true);
                $cli->print("");
            }
        });

        $this->events()->afterExec()->listen(function () {
            $displayErrors = @constant($this->execClassName . "::DISPLAY_TRIGGERED_ERRORS") ?? true;
            if ($displayErrors) {
                $errors = $this->app()->errorHandler()->errors()->all();
                $errorsCount = $this->app->errorHandler()->errors()->count();

                $this->print("");
                if ($errorsCount) {
                    $this->repeat(".", 10, 50, true);
                    $this->print("");
                    $this->print(sprintf("{red}{invert} %d {/}{red}{b} triggered errors!{/}", $errorsCount));
                    foreach ($errors as $error) {
                        $this->print(sprintf('{grey}│  ┌ {/}{yellow}Type:{/} {magenta}%s{/}', strtoupper($error["type"])));
                        $this->print(sprintf('{grey}├──┼ {/}{yellow}Message:{/} %s', $error["message"]));
                        $this->print(sprintf("{grey}│  ├ {/}{yellow}File:{/} {cyan}%s{/}", $error["file"]));
                        $this->print(sprintf("{grey}│  └ {/}{yellow}Line:{/} %d", $error["line"] ?? -1));
                        $this->print("{grey}│{/}");
                    }

                    $this->print("");
                } else {
                    $this->print("{grey}No triggered errors!{/}");
                }
            }
        });
    }

    /**
     * @return void
     */
    public function printAppHeader(): void
    {
        $this->print(sprintf("{yellow}{invert}Comely App Kernel{/} {grey}v%s{/}", AppKernel::VERSION), 200);
        $this->print(sprintf("{cyan}{invert}Comely CLI{/} {grey}v%s{/}", \Comely\CLI\CLI::VERSION), 200);

        // App Introduction
        $this->print("");
        $this->repeat("~", 5, 100, true);
        foreach (Banners::Digital($this->app->constant("name") ?? "Untitled App")->lines() as $line) {
            $this->print("{magenta}{invert}" . $line . "{/}");
        }

        $this->repeat("~", 5, 100, true);
        $this->print("");
    }

    /**
     * @return AppKernel
     */
    public function app(): AppKernel
    {
        return $this->app;
    }
}