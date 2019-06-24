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

namespace Comely\App\Config;

use Comely\App\Exception\AppConfigException;
use Comely\Utils\OOP\ObjectMapper\Exception\ObjectMapperException;

/**
 * Class MailerConfig
 * @package Comely\App\Config
 */
class MailerConfig
{
    /** @var string */
    private $agent;
    /** @var MailerSMTPConfig */
    private $smtp;

    /**
     * MailerConfig constructor.
     * @param array $mailer
     * @throws AppConfigException
     */
    public function __construct(array $mailer)
    {
        // Agent
        $agent = $mailer["agent"];
        if (!is_string($agent) || !preg_match('/\w+/', $agent)) {
            throw new AppConfigException('Invalid agent value for mailer service');
        }

        $this->agent = strtolower($agent);
        if (!in_array($this->agent, ["sendmail", "smtp"])) {
            throw new AppConfigException('No such mailer agent is supported');
        }

        // SMTP
        $smtp = $mailer["smtp"] ?? null;
        if (is_array($smtp)) {
            try {
                $this->smtp = new MailerSMTPConfig($smtp);
            } catch (ObjectMapperException $e) {
                throw new AppConfigException($e->getMessage());
            }
        }
    }

    /**
     * @param string $prop
     * @return mixed
     */
    public function __get(string $prop)
    {
        switch ($prop) {
            case "agent":
                return $this->agent;
        }

        throw new \DomainException('Cannot get value for inaccessible property');
    }

    /**
     * @return MailerSMTPConfig|null
     */
    public function smtp(): ?MailerSMTPConfig
    {
        return $this->smtp;
    }
}