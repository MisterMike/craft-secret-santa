<?php
namespace nibiru\secretsanta\services;

use Craft;
use craft\base\Component;
use nibiru\secretsanta\models\SantaMember;
use nibiru\secretsanta\records\SantaMemberRecord;

class MemberService extends Component
{
    public function addMember(int $groupId, int $userId): void
    {

        $exists = SantaMemberRecord::find()
            ->where(['groupId' => $groupId, 'userId' => $userId])
            ->exists();

        if ($exists) {
            Craft::$app->session->setNotice("User already in group.");
            return;
        }

        $record = new SantaMemberRecord();
        $record->groupId = $groupId;
        $record->userId = $userId;
        $record->token = Craft::$app->security->generateRandomString(48);
        $record->save();
    }

    public function getMemberById(int $id): ?SantaMember
    {
        $record = SantaMemberRecord::find()
            ->where(['id' => $id])
            ->one();

        if (!$record) {
            return null;
        }

        return new SantaMember($record->toArray());
    }


    public function removeMemberById(int $groupId, int $userId): int
    {
        return SantaMemberRecord::deleteAll([
            'groupId' => $groupId,
            'userId' => $userId,
        ]);
    }



    public function getMembersByGroupId(int $groupId): array
    {
        $records = SantaMemberRecord::find()
            ->where(['groupId' => $groupId])
            ->all();

        return array_map(fn($record) => $this->createModelFromRecord($record), $records);
    }


    public function getByToken(string $token)
    {
        return SantaMemberRecord::find()->where(['token' => $token])->one();
    }

    
    public function getById(int $id): ?SantaMember
    {
        $record = SantaMemberRecord::findOne($id);
        return $record ? $this->createModelFromRecord($record) : null;
    }


    /* Private Stuff */

    private function createModelFromRecord(SantaMemberRecord $record): SantaMember
    {
        return new SantaMember([
            'id' => $record->id,
            'groupId' => $record->groupId,
            'userId' => $record->userId,
            'dateInvited' => $record->dateInvited,
            'dateAccepted' => $record->dateAccepted,
            'dateDrawn' => $record->dateDrawn,
            'wishlist' => $record->wishlist,
            'wishlistRefused' => (bool)$record->wishlistRefused,
            'drawnMemberId' => $record->drawnMemberId,
        ]);
    }
}
