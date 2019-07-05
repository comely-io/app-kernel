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

namespace Comely\App\CLI;

use Comely\App\AppKernel;
use Comely\App\CLI;
use Comely\CLI\Abstract_CLI_Script;

/**
 * Class AbstractAppScript
 * @package Comely\App\CLI
 */
abstract class AbstractAppScript extends Abstract_CLI_Script
{
    /** @var AppKernel */
    protected $app;
    /** @var CLI */
    protected $cli;

    /**
     * AbstractAppScript constructor.
     * @param CLI $cli
     */
    public function __construct(CLI $cli)
    {
        parent::__construct($cli);
        if (!$this->app instanceof AppKernel) {
            $this->app = AppKernel::getInstance();
        }
    }
}