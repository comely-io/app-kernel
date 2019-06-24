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
 * Class ServicesConfig
 * @package Comely\App\Config
 */
class ServicesConfig
{
    /** @var CacheServiceConfig|null */
    private $cache;
    private $cipher;
    /** @var HttpServiceConfig|null */
    private $http;
    /** @var SessionsServiceConfig|null */
    private $sessions;
    /** @var MailerConfig|null */
    private $mailer;
    /** @var TranslatorServiceConfig|null */
    private $translator;

    /**
     * ServicesConfig constructor.
     * @param array $services
     * @throws AppConfigException
     */
    public function __construct(array $services)
    {
        // Cache
        $cacheConfig = $services["cache"] ?? null;
        if (is_array($cacheConfig)) {
            try {
                $this->cache = new CacheServiceConfig($cacheConfig);
            } catch (ObjectMapperException $e) {
                throw new AppConfigException($e->getMessage());
            }
        }

        // Cipher Config
        $cipherConfig = $services["cipher"] ?? null;
        if (is_array($cipherConfig)) {
            $this->cipher = new CipherConfig($cipherConfig);
        }

        // Http
        $httpConfig = $services["http"] ?? null;
        if (is_array($httpConfig)) {
            $this->http = new HttpServiceConfig($httpConfig);
        }

        // Sessions
        $sessionsConfig = $services["sessions"] ?? null;
        if (is_array($sessionsConfig)) {
            try {
                $this->sessions = new SessionsServiceConfig($sessionsConfig);
            } catch (ObjectMapperException $e) {
                throw new AppConfigException($e->getMessage());
            }
        }

        // Mailer
        $mailerConfig = $services["mailer"] ?? null;
        if (is_array($mailerConfig)) {
            $this->mailer = new MailerConfig($mailerConfig);
        }

        // Translator
        $translatorConfig = $services["translator"] ?? null;
        if (is_array($translatorConfig)) {
            try {
                $this->translator = new TranslatorServiceConfig($translatorConfig);
            } catch (ObjectMapperException $e) {
                throw new AppConfigException($e->getMessage());
            }
        }
    }

    /**
     * @return CacheServiceConfig|null
     */
    public function cache(): ?CacheServiceConfig
    {
        return $this->cache;
    }

    /**
     * @return CipherConfig|null
     */
    public function cipher(): ?CipherConfig
    {
        return $this->cipher;
    }

    /**
     * @return HttpServiceConfig|null
     */
    public function http(): ?HttpServiceConfig
    {
        return $this->http;
    }

    /**
     * @return SessionsServiceConfig|null
     */
    public function sessions(): ?SessionsServiceConfig
    {
        return $this->sessions;
    }

    /**
     * @return MailerConfig|null
     */
    public function mailer(): ?MailerConfig
    {
        return $this->mailer;
    }

    /**
     * @return TranslatorServiceConfig|null
     */
    public function translator(): ?TranslatorServiceConfig
    {
        return $this->translator;
    }
}