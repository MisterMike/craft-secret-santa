<?php
namespace nibiru\secretsanta\controllers;

use Craft;
use craft\web\Controller;
use nibiru\secretsanta\SecretSanta;

class PublicController extends Controller
{
    protected int|bool|array $allowAnonymous = false;

    public function actionLanding(string $token)
    {
        $member = SecretSanta::$plugin->member->getByToken($token);

        if (!$member) {
            throw new \yii\web\NotFoundHttpException("Invalid token.");
        }

        // Load Craft User element
        $user = Craft::$app->users->getUserById($member->userId);

        // Load SecretSanta Group (your own record/model)
        $group = SecretSanta::$plugin->group->getById($member->groupId);

        return $this->renderTemplate('_pages/wichteln/member', [
            'member' => $member,
            'user'   => $user,
            'group'  => $group,
        ]);
    }


    public function actionSaveWishlist()
    {
        $token = Craft::$app->request->getBodyParam('token');
        $wishlist = Craft::$app->request->getBodyParam('wishlist');

        $member = SecretSanta::$plugin->member->getByToken($token);
        $member->wishlist = $wishlist;
        $member->save();

        return $this->redirect("/santa/$token");
    }
}
