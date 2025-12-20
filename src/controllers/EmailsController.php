<?php

namespace nibiru\secretsanta\controllers;

use Craft;
use craft\helpers\StringHelper;
use craft\web\Controller;
use nibiru\secretsanta\elements\SantaEmailTemplateElement;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class EmailsController extends Controller
{
    public function actionEdit(int $emailId = null): Response
    {
        $this->requireCpRequest();

        if ($emailId) {
            $email = SantaEmailTemplateElement::find()
                ->id($emailId)
                ->one();

            if (!$email) {
                throw new NotFoundHttpException('Email template not found.');
            }
        } else {
            $email = new SantaEmailTemplateElement();
            $email->enabled = true;
        }

        return $this->renderTemplate('secret-santa/emails/_edit', [
            'email' => $email,
            'isNew' => !$email->id,
        ]);
    }

    public function actionSave(): Response
    {
        $this->requireCpRequest();
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $emailId = $request->getBodyParam('emailId');

        if ($emailId) {
            $email = SantaEmailTemplateElement::find()
                ->id((int)$emailId)
                ->one();

            if (!$email) {
                throw new NotFoundHttpException('Email template not found.');
            }
        } else {
            $email          = new SantaEmailTemplateElement();
        }

        $email->title       = $request->getBodyParam('title');
        $email->title       = (string)($request->getBodyParam('title') ?? '');
        $email->handle      = (string)($request->getBodyParam('handle') ?? '');
        $email->enabled     = (bool)$request->getBodyParam('enabled');
        $email->subject     = (string)($request->getBodyParam('subject') ?? '');
        $email->mjmlBody    = (string)($request->getBodyParam('mjmlBody') ?? '');
        $email->enabled     = (bool)$request->getBodyParam('enabled');

        if (!$email->handle && $email->title) {
            $email->handle  = StringHelper::toHandle($email->title);
        }

        if (!Craft::$app->getElements()->saveElement($email)) {
            Craft::$app->getSession()->setError(
                Craft::t('secret-santa', 'Could not save email template.')
            );

            return $this->renderTemplate('secret-santa/emails/_edit', [
                'email' => $email,
                'isNew' => !$email->id,
            ]);
        }

        Craft::$app->getSession()->setNotice(
            Craft::t('secret-santa', 'Email template saved.')
        );

        return $this->redirect('secret-santa/emails/' . $email->id);
    }
}
