<?php

namespace nibiru\secretsanta\services;

use Craft;
use craft\base\Component;
use nibiru\secretsanta\elements\SantaGroupElement;
use yii\base\Exception;

class GroupGuardService extends Component
{

    public function canModify(SantaGroupElement $group): bool
    {
        return $group->groupStatus !== SantaGroupElement::STATUS_DRAWN;
    }

    public function ensureCanModify(SantaGroupElement $group): void
    {
        if (!$this->canModify($group)) {
            throw new ForbiddenHttpException(
                Craft::t('secret-santa', 'This group can no longer be modified.')
            );
        }
    }

    public function canDraw(SantaGroupElement $group): bool
    {
        return
            $group->groupStatus === SantaGroupElement::STATUS_READY
            && $group->getMembersCount() >= 2
            && $group->allMembersAccepted($group);
    }


    public function ensureCanDraw(SantaGroupElement $group): void
    {
        if (!$this->canDraw($group)) {
            throw new ForbiddenHttpException(
                Craft::t('secret-santa', 'This group cannot be drawn yet.')
            );
        }
    }

    public function canResendDraw(SantaGroupElement $group): bool
    {
        return in_array($group->groupStatus, ['drawn', 'completed'], true);
    }

}

