<?php

/**
 * Secret Santa Plugin for Craft CMS 5.x
 *
 * Secret Santa Plugin
 *
 * @link      https://www.marukka.ch/plugins
 * @copyright Copyright (c) 2025 Mischa Sprecher
 */

namespace nibiru\secretsanta\models;

use Craft;
use craft\base\Model;
use craft\validators\TemplateValidator;

/**
 * Secret Santa settings
 */
class SettingsModel extends Model
{
    // Properties
    // =========================================================================

    public string $pluginName = 'Secret Santa';

    /**
     * @var string|null The template that emails should be sent with
     */
    public ?string $mailtemplate = null;

    /**
     * @var string|null The default email address that emails should be sent from
     */
    public ?string $fromEmail = null;

    /**
     * @var string|null The default name that emails should be sent from
     */
    public ?string $fromName = null;

    public function attributeLabels(): array
    {
        return [
            'fromEmail' => Craft::t('secret-santa', 'System Email Address'),
            'fromName' => Craft::t('secret-santa', 'Sender Name'),
            'mailtemplate' => Craft::t('secret-santa', 'HTML Email Template'),
        ];
    }

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['fromEmail', 'fromName'], 'required'];
        $rules[] = [['fromEmail'], 'email'];
        $rules[] = [['mailtemplate'], TemplateValidator::class];

        return $rules;
    }
}
