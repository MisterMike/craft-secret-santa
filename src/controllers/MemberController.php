<?php
namespace nibiru\secretsanta\controllers;

use Craft;
use craft\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use nibiru\secretsanta\SecretSanta;

class MemberController extends Controller
{
    // CP only
    protected array|int|bool $allowAnonymous = false;

    public function actionIndex(int $groupId, int $memberId): Response
    {
        $groupsService = SecretSanta::getInstance()->group;
        $membersService = SecretSanta::getInstance()->member;

        $group = $groupsService->getGroupElementById($groupId);
        if (!$group) {
            throw new NotFoundHttpException('Group not found');
        }

        $member = $membersService->getMemberById($memberId);
        if (!$member || (int)$member->groupId !== (int)$groupId) {
            throw new NotFoundHttpException('Member not found in this group');
        }

        return $this->renderTemplate('secret-santa/member/index', [
            'group'  => $group,
            'member' => $member,
        ]);
    }
}
