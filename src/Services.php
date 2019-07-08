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

use Comely\Cache\Cache;
use Comely\Http\Exception\ServiceNotConfiguredException;
use Comely\Http\Router;
use Comely\Knit\Knit;
use Comely\Mailer\Agents\SMTP;
use Comely\Mailer\Mailer;
use Comely\Sessions\Sessions;
use Comely\Sessions\Storage\SessionDirectory;
use Comely\Translator\Exception\TranslatorException;
use Comely\Translator\Translator;

/**
 * Class Services
 * @package Comely\App
 */
class Services
{
    /** @var AppKernel */
    private $appKernel;
    /** @var Cache */
    private $cache;
    /** @var Router */
    private $router;
    /** @var Sessions */
    private $sessions;
    /** @var Knit */
    private $knit;
    /** @var Translator */
    private $translator;
    /** @var Mailer */
    private $mailer;

    /**
     * Services constructor.
     * @param AppKernel $appKernel
     */
    public function __construct(AppKernel $appKernel)
    {
        $this->appKernel = $appKernel;
    }

    /**
     * @return Cache
     * @throws ServiceNotConfiguredException
     * @throws \Comely\Cache\Exception\ConnectionException
     */
    public function cache(): Cache
    {
        if (!$this->cache) {
            $cacheConfig = $this->appKernel->config()->services()->cache();
            if (!$cacheConfig) {
                throw new ServiceNotConfiguredException('Cache service is not configured');
            }

            if (!$cacheConfig->engine) {
                throw new ServiceNotConfiguredException('Cache service is disabled; No engine configured');
            }

            $cache = new Cache();
            $cache->servers()->add($cacheConfig->engine, $cacheConfig->host, $cacheConfig->port);
            $cache->connect();
            $this->cache = $cache;
        }

        return $this->cache;
    }

    /**
     * @return Sessions
     * @throws Exception\AppDirectoryException
     * @throws ServiceNotConfiguredException
     * @throws \Comely\Sessions\Exception\StorageException
     */
    public function sessions(): Sessions
    {
        $sessionsConfig = $this->appKernel->config()->services()->sessions();
        if (!$sessionsConfig) {
            throw new ServiceNotConfiguredException('Sessions service is not configured');
        }

        if (!$this->sessions) {
            $sessionsDirectory = new SessionDirectory($this->appKernel->dirs()->sessions());
            $sessions = new Sessions($sessionsDirectory);
            $this->sessions = $sessions;
        }

        return $this->sessions;
    }

    /**
     * @return Router
     * @throws \Comely\Http\Exception\RouterException
     */
    public function router(): Router
    {
        if (!$this->router) {
            $this->router = new Router();
        }

        return $this->router;
    }

    /**
     * @return Knit
     * @throws Exception\AppDirectoryException
     */
    public function knit(): Knit
    {
        if ($this->knit) {
            return $this->knit;
        }

        $knit = new Knit();
        $knit->dirs()->cache($this->appKernel->dirs()->cache())
            ->compiler($this->appKernel->dirs()->compiler())
            ->templates($this->appKernel->dirs()->templates());

        $knit->modifiers()->registerDefaultModifiers();

        $this->knit = $knit;
        return $this->knit;
    }

    /**
     * @return Translator
     * @throws Exception\AppDirectoryException
     * @throws ServiceNotConfiguredException
     * @throws TranslatorException
     */
    public function translator()
    {
        if ($this->translator) {
            return $this->translator;
        }

        try {
            $translatorConfig = $this->appKernel->config()->services()->translator();
            if (!$translatorConfig) {
                throw new ServiceNotConfiguredException('Translator service is not configured');
            }

            $this->translator = Translator::createInstance($this->appKernel->dirs()->langs());
            if ($translatorConfig->caching) {
                $this->translator->cachingDirectory($this->appKernel->dirs()->cache());
            }

            // Fallback language
            if ($translatorConfig->fallback) {
                $this->translator->fallback($translatorConfig->fallback);
            }

            // Current language
            if ($translatorConfig->cookie) {
                $langCookie = $_COOKIE[$translatorConfig->cookie] ?? null;
                if (is_string($langCookie) && $langCookie) {
                    $this->translator->language($langCookie);
                }
            }
        } catch (TranslatorException $e) {
            throw $e;
        }

        return $this->translator;
    }

    /**
     * @return Mailer
     * @throws ServiceNotConfiguredException
     * @throws \Comely\Mailer\Exception\InvalidEmailAddrException
     */
    public function mailer()
    {
        if ($this->mailer) { // Already registered?
            return $this->mailer;
        }

        $mailerConfig = $this->appKernel->config()->services()->mailer();
        if (!$mailerConfig) {
            throw new ServiceNotConfiguredException('Mailer service is not configured');
        }

        $mailer = new Mailer();
        $mailer->sender()
            ->name($mailerConfig->senderName)
            ->email($mailerConfig->senderEmail);

        // Agent
        if ($mailerConfig->agent === "smtp") {
            $smtpConfig = $mailerConfig->smtp();
            if (!$smtpConfig) {
                throw new ServiceNotConfiguredException('SMTP agent is not configured');
            }

            $username = $smtpConfig->username;
            $password = $smtpConfig->password;
            $serverName = $smtpConfig->serverName;

            $smtp = (new SMTP($smtpConfig->host, $smtpConfig->port, $smtpConfig->timeOut))
                ->useTLS($smtpConfig->tls);
            if ($username && $password) { // Authentication credentials
                $smtp->authCredentials($username, $password);
            }

            if ($serverName) { // Server Name
                $smtp->serverName($serverName);
            }

            $mailer->agent($smtp); // Bind agent
        }

        $this->mailer = $mailer;
        return $this->mailer;
    }
}