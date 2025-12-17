<?php

namespace nibiru\secretsanta\controllers;

use Craft;
use craft\web\Controller;

use nibiru\secretsanta\SecretSanta;
use nibiru\secretsanta\elements\SantaGroupElement;

use yii\web\NotFoundHttpException;
use yii\web\Response;

class GroupsController extends Controller
{
    protected array|bool|int $allowAnonymous = false;

    public function init(): void
    {
        parent::init();
        $this->requireCpRequest();
    }

    /* ================= INDEX ================= */

    public function actionIndex(): Response
    {
        return $this->renderTemplate('secret-santa/groups/index');
    }

    /* ================= EDIT ================= */

    public function actionEdit(?int $groupId = null): Response
    {
        if ($groupId) {
            $group = SantaGroupElement::find()->id($groupId)->one();
            if (!$group) {
                throw new NotFoundHttpException('Group not found');
            }

            $members = SecretSanta::getInstance()->member->getMembersByGroupId($groupId);
        } else {
            $group = new SantaGroupElement();
            $members = [];
        }

        return $this->renderTemplate('secret-santa/groups/_edit', [
            'element' => $group,
            'group' => $group,
            'members' => $members,
        ]);
    }



    /* ================= SAVE ================= */

    public function actionSave(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->request;

        $groupId = $request->getBodyParam('groupId');
        $title = trim((string)$request->getBodyParam('title'));

        $group = $groupId
            ? SantaGroupElement::find()->id((int)$groupId)->one()
            : new SantaGroupElement();

        if (!$group) {
            throw new NotFoundHttpException('Group not found');
        }

        $group->title = $title;

        if (!Craft::$app->elements->saveElement($group)) {
            Craft::$app->session->setError(Craft::t('app', 'Couldn’t save group.'));
            return $this->redirectToPostedUrl();
        }

        Craft::$app->session->setNotice(Craft::t('app', 'Group saved.'));

        return $this->redirect('secret-santa/groups');
    }
}
