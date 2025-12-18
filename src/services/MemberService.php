<?php
namespace nibiru\secretsanta\services;

use Craft;
use craft\base\Component;
use nibiru\secretsanta\SecretSanta;
use nibiru\secretsanta\elements\SantaGroupElement;
use nibiru\secretsanta\models\SantaMember;
use nibiru\secretsanta\records\SantaMemberRecord;

class MemberService extends Component
{
public function addMember(int $groupId, int $userId): void
    {
        $group = SantaGroupElement::find()
            ->id($groupId)
            ->one();

        if (!$group) {
            throw new NotFoundHttpException('Group not found');
        }

        // can we add the member ?
        SecretSanta::$plugin->groupGuard->canAddMember($group);

        $exists = SantaMemberRecord::find()
            ->where([
                'groupId' => $group->id,
                'userId' => $userId,
            ])
            ->exists();

        if ($exists) {
            Craft::$app->session->setNotice(
                Craft::t('secret-santa', 'User already in group.')
            );
            return;
        }

        $record = new SantaMemberRecord();
        $record->groupId = $group->id;
        $record->userId = $userId;
        $record->token = Craft::$app->security->generateRandomString(48);
        $record->save();

        $this->updateGroupStatus($group);
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
        $this->updateGroupStatus($groupId);

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

    private function updateGroupStatus(SantaGroupElement $group): void
    {
        $count = $group->getMembersCount();

        if ($count < 2) {
            $group->groupStatus = 'draft';
        } elseif ($group->groupStatus === 'draft') {
            $group->groupStatus = 'ready';
        }

        SecretSanta::info("updateGroupStatus:".$group->groupStatus);

        Craft::$app->getElements()->saveElement($group, false);
    }

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
