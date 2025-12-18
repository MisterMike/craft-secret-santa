<?php

declare(strict_types=1);
/**
 * Secret Santa Plugin for Craft CMS 5.x
 *
 * Secret Santa Plugin
 *
 * @link      https://www.marukka.ch/plugins
 * @copyright Copyright (c) 2025 Mischa Sprecher
 */

namespace nibiru\secretsanta\traits;

use nibiru\secretsanta\SecretSanta;

use nibiru\secretsanta\services\EmailService;
use nibiru\secretsanta\services\DrawService;
use nibiru\secretsanta\services\GroupService;
use nibiru\secretsanta\services\GroupGuardService;
use nibiru\secretsanta\services\MemberService;

use yii\base\InvalidConfigException;

trait PluginTrait
{
    use LogTrait;
    
    // Properties
    // =========================================================================

    /**
     * @var SecretSanta
     */
    public static ?SecretSanta $plugin = null;

    /**
     * @throws InvalidConfigException
     */
    public function getDraw(): DrawService
    {
        return $this->get('draw');
    }

    /**
     * @throws InvalidConfigException
     */
    public function getEmail(): EmailService
    {
        return $this->get('email');
    }

    /**
     * @throws InvalidConfigException
     */
    public function getGroup(): GroupService
    {
        return $this->get('group');
    }

    /**
     * @throws InvalidConfigException
     */
    public function getGroupGuard(): GroupGuardService
    {
        return $this->get('groupGuard');
    }

    /**
     * @throws InvalidConfigException
     */
    public function getMember(): MemberService
    {
        return $this->get('member');
    }
}
