<?php
namespace nibiru\secretsanta\controllers;

use Craft;
use craft\elements\User;
use craft\web\Controller;
use craft\web\Response; 

use nibiru\secretsanta\SecretSanta;
use nibiru\secretsanta\elements\SantaGroupElement;

class AdminController extends Controller
{
    protected int|bool|array $allowAnonymous = false;

    public function actionIndex(): Response
    {
        $groups = SecretSanta::$plugin->group->getAll();
        return $this->renderTemplate('secret-santa/admin/index', [
            'groups' => $groups,
        ]);
    }

    public function actionGroup(int $groupId): Response
    {
        $group      = SecretSanta::$plugin->group->getById($groupId);
        $members    = SecretSanta::$plugin->member->getMembersByGroup($groupId);
        $allUsers   = \craft\elements\User::find()
                        ->status('active')
                        ->all();

        return $this->renderTemplate('secret-santa/admin/group', [
            'group'     => $group,
            'members'   => $members,
            'allUsers'  => $allUsers,
        ]);
    }

    public function actionNewGroup()
    {
        return $this->renderTemplate('secret-santa/admin/new');
    }


    public function actionDraw(int $groupId): Response
    {
        SecretSanta::$plugin->draw->runDraw($groupId);
        Craft::$app->session->setNotice("Draw completed!");
        return $this->redirect("secret-santa/group/{$groupId}");
    }

    /*
     * Show the user detail page 
    */
    public function actionMember(int $groupId, int $memberId): Response
    {
        $group = SecretSanta::$plugin->group->getById($groupId);
        $member = SecretSanta::$plugin->member->getById($memberId);

        if (!$group || !$member || $member->groupId !== $groupId) {
            throw new NotFoundHttpException('Member not found.');
        }

        $user = Craft::$app->users->getUserById($member->userId);

        return $this->renderTemplate('secret-santa/admin/member', [
            'group'  => $group,
            'member' => $member,
            'user'   => $user,
        ]);
    }


    public function actionAddMember(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $groupId = (int)$request->getRequiredBodyParam('groupId');
        $userId  = (int)$request->getRequiredBodyParam('userId');

        // Resolve group once, early
        $group = SantaGroupElement::find()
            ->id($groupId)
            ->one();

        if (!$group) {
            throw new NotFoundHttpException('Group not found.');
        }

        // No changes allowed after draw has taken place
        if ($group->groupStatus === 'drawn') {
            Craft::$app->session->setError(
                Craft::t('secret-santa', 'Members cannot be added after names have been drawn.')
            );

            return $this->redirect("secret-santa/group/{$groupId}");
        }

        SecretSanta::$plugin->member->addMember($groupId, $userId);

        Craft::$app->session->setNotice(
            Craft::t('secret-santa', 'Member added.')
        );

        return $this->redirect("secret-santa/group/{$groupId}");
    }


    // public function actionRemoveMember()
    // {
    //     $this->requirePostRequest();

    //     $userId     = Craft::$app->request->getRequiredBodyParam('userId');
    //     $groupId    = Craft::$app->request->getRequiredBodyParam('groupId');

    //     SecretSanta::$plugin->member->removeMemberById($groupId,$userId);

    //     Craft::$app->session->setNotice('Member removed.');
    //     return $this->redirect("secret-santa/group/{$groupId}");
    // }

    public function actionCreateGroup()
    {
        $this->requirePostRequest();

        $title = Craft::$app->request->getRequiredBodyParam('title');

        $group = SecretSanta::$plugin->group->create($title);

        $notice = Craft::t('secret-santa', 'Group created successfully.');

        Craft::$app->session->setNotice($notice);
        return $this->redirect("secret-santa/group/" . $group->id);
    }


    public function actionSendInvitations()
    {
        $groupId = Craft::$app->request->getRequiredParam('groupId');
        $members = SecretSanta::$plugin->member->getMembersByGroup($groupId);
        $group = SecretSanta::$plugin->group->getById($groupId);

        foreach ($members as $member) {
            SecretSanta::$plugin->email->sendInvitation($member, $group);
        }

        Craft::$app->session->setNotice("Invitations sent.");
        return $this->redirect("secret-santa/group/{$groupId}");
    }


}
