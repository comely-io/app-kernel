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

namespace Comely\App\Http\Security;

use Comely\App\AppKernel;
use Comely\Sessions\ComelySession;

/**
 * Class Forms
 * @package Comely\App\Http\Security
 */
class Forms
{
    /** @var AppKernel */
    private $appKernel;
    /** @var ComelySession */
    private $session;

    /**
     * Forms constructor.
     * @param AppKernel $appKernel
     * @param ComelySession $session
     */
    public function __construct(AppKernel $appKernel, ComelySession $session)
    {
        $this->appKernel = $appKernel;
        $this->session = $session;
    }

    /**
     * @param string $name
     * @param array $fields
     * @return ObfuscatedForm
     */
    public function get(string $name, array $fields): ObfuscatedForm
    {
        return $this->retrieve($name) ?? $this->obfuscate($name, $fields);
    }

    /**
     * @param string $name
     * @param array $fields
     * @return ObfuscatedForm
     */
    public function obfuscate(string $name, array $fields): ObfuscatedForm
    {
        try {
            $form = new ObfuscatedForm($name, $fields);
        } catch (\RuntimeException $e) {
            if ($e->getCode() === ObfuscatedForm::SIGNAL_RETRY) {
                return $this->obfuscate($name, $fields);
            }

            throw $e;
        }

        $this->session->meta()->bag("obfuscated_forms")->set($form->name(), serialize($form));
        return $form;
    }

    /**
     * @param string $name
     * @return ObfuscatedForm|null
     */
    public function retrieve(string $name): ?ObfuscatedForm
    {
        if (!preg_match('/^\w{3,32}$/', $name)) {
            throw new \InvalidArgumentException('Invalid obfuscated form name');
        }

        $form = $this->session->meta()->bag("obfuscated_forms")->get($name);
        if (!$form) {
            return null;
        }

        $form = unserialize(strval($form), [
            "allowed_classes" => [
                'Comely\App\Http\Security\ObfuscatedForm'
            ]
        ]);

        if (!$form instanceof ObfuscatedForm) {
            trigger_error(sprintf('Failed to unserialize obfuscated form "%s"', $name), E_USER_WARNING);
            $this->purge($name);
            return null;
        }

        return $form;
    }

    /**
     * @param string $name
     */
    public function purge(string $name): void
    {
        $this->session->meta()->bag("obfuscated_forms")->delete($name);
    }

    /**
     * @return void
     */
    public function flush(): void
    {
        $this->session->meta()->delete("obfuscated_forms");
    }
}