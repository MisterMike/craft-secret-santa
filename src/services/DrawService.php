<?php
namespace nibiru\secretsanta\services;

use Craft;
use craft\base\Component;
use nibiru\secretsanta\records\SantaMemberRecord;

class DrawService extends Component
{
    public function runDraw(int $groupId): bool
    {
        $members = SantaMemberRecord::find()
            ->where(['groupId' => $groupId, 'status' => 'accepted'])
            ->all();

        if (count($members) < 3) {
            return false;
        }

        shuffle($members);

        for ($i = 0; $i < count($members); $i++) {
            $giver = $members[$i];
            $receiver = $members[($i + 1) % count($members)];
            $giver->drawnMemberId = $receiver->userId;
            $giver->save();
        }

        $group->groupStatus = 'drawn';
        Craft::$app->getElements()->saveElement($group, false);

        SecretSanta::$plugin->emails->sendAssignments($members);

        return true;
    }
}
