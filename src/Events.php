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

use Comely\App\Traits\NotCloneableTrait;
use Comely\App\Traits\NotSerializableTrait;
use Comely\Utils\Events\EventsRegister;

/**
 * Class Events
 * @package Comely\App
 */
class Events
{
    /** @var AppKernel */
    private $appKernel;
    /** @var EventsRegister */
    private $events;

    use NotSerializableTrait;
    use NotCloneableTrait;

    /**
     * Events constructor.
     * @param AppKernel $appKernel
     */
    public function __construct(AppKernel $appKernel)
    {
        $this->appKernel = $appKernel;
        $this->events = new EventsRegister();
    }

    /**
     * @return EventsRegister
     */
    public function register(): EventsRegister
    {
        return $this->events;
    }
}