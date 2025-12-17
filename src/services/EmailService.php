<?php
namespace nibiru\secretsanta\services;

use Craft;
use craft\base\Component;
use craft\web\View;
use nibiru\secretsanta\SecretSanta;
use superbig\mjml\MJML;

class EmailService extends Component
{
    public function sendInvitation($member, $group)
    {
        $user = Craft::$app->users->getUserById($member->userId);
        if (!$user) return false;

        $url = Craft::$app->sites->getCurrentSite()->getBaseUrl() . 'santa/' . $member->token;

        // get the template if we don't have one passed in and fallback hard
        $htmlTemplate = Craft::parseEnv(SecretSanta::getInstance()->getSettings()->mailtemplate ?? '')
            ?: '_emails/wichteln/base.twig';

        if (! $htmlTemplate) {
            throw new InvalidConfigException('EmailsService: htmlTemplate missing.');
        }
        if (! $user || !$member || !$group) {
            throw new InvalidConfigException('EmailsService: user, member and groupd are required.');
        }

        SecretSanta::info("Test Log.");

        // Render Twig but use site mode (not plugin)
        $view = Craft::$app->getView();
        $oldMode = $view->getTemplateMode();
        $view->setTemplateMode(View::TEMPLATE_MODE_SITE);

        try {

            // Check template
            if (! $view->doesTemplateExist($htmlTemplate)) {
                throw new InvalidConfigException("EmailsService: template '{$htmlTemplate}' not found.");
            }

            // hydrate template
            $html = $view->renderTemplate($htmlTemplate, [
                'user' => $user,
                'group' => $group,
                'url' => $url,
                'emailSubject' => 'Einladung zum Wichteln',
                'emailType' => 'invitation',
            ]);

            // Compile MJML (if present)
            if (stripos($html, '<mjml') !== false) {
                $compiled = MJML::$plugin->mjmlService->parse($html);
                if ($compiled) {
                    $html = (string) $compiled->output();
                }
            }
        } finally {
            $view->setTemplateMode($oldMode);
        }


        return Craft::$app->mailer
            ->compose()
            ->setFrom(['mischa@parhelia.ch' => 'Basler Wichtelmeister',])
            ->setTo($user->email)
            ->setSubject("Einladung zum Wichteln")
            ->setHtmlBody($html)
            ->send();
    }

    public function sendAssignment($member, $recipient)
    {
        $user = Craft::$app->users->getUserById($member->userId);

        // Render Twig but use site mode (not plugin)
        $view = Craft::$app->getView();
        $oldMode = $view->getTemplateMode();
        $view->setTemplateMode(View::TEMPLATE_MODE_SITE);

        try {

            // Check template
            if (! $view->doesTemplateExist($htmlTemplate)) {
                throw new InvalidConfigException("EmailsService: template '{$htmlTemplate}' not found.");
            }

            // hydrate template
            $html = $view->renderTemplate($htmlTemplate, [
                'user' => $user,
                'group' => $group,
                'url' => $url,
                'emailSubject' => 'Wichtelauslosung - Deine Zuteilung',
                'emailType' => 'assignment',
            ]);

            // Compile MJML (if present)
            if (stripos($html, '<mjml') !== false) {
                $compiled = MJML::$plugin->mjmlService->parse($html);
                if ($compiled) {
                    $html = (string) $compiled->output();
                }
            }
        } finally {
            $view->setTemplateMode($oldMode);
        }

        return Craft::$app->mailer
            ->compose()
            ->setFrom(['mischa@parhelia.ch' => 'Basler Wichtelmeister',])
            ->setTo($user->email)
            ->setSubject("Wichtelauslosung - Deine Zuteilung")
            ->setHtmlBody($html)
            ->send();
    }
}
