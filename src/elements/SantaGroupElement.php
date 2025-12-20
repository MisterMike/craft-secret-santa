<?php

namespace nibiru\secretsanta\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\enums\Color;
use craft\helpers\Cp;
use craft\helpers\Db;
use craft\helpers\UrlHelper;

use nibiru\secretsanta\SecretSanta;
use nibiru\secretsanta\elements\db\SantaGroupQuery;
use nibiru\secretsanta\records\SantaMemberRecord;

use yii\web\ForbiddenHttpException;

class SantaGroupElement extends Element
{
    public ?string $title       = '';
    public bool $enabled        = true;
    public string $groupStatus  = 'draft';

    public const STATUS_DRAFT  = 'draft';
    public const STATUS_READY  = 'ready';
    public const STATUS_DRAWN  = 'drawn';

    /* ================== Meta ================== */

    public static function displayName(): string
    {
        return Craft::t('secret-santa', 'Gruppe');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('secret-santa', 'Secret Santa Groups');
    }

    public static function tableName(): string
    {
        return '{{%santa_groups}}';
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasUris(): bool
    {
        return true;
    }

    public static function hasContent(): bool
    {
        return false;
    }

    /**
     * Central mapping: status -> CP label + color (+ optional icon)
     */
    public static function statusBadges(): array
    {
        return [
            self::STATUS_DRAFT => [
                'label' => Craft::t('secret-santa', 'Draft'),
                'color' => Color::Orange,
                'icon'  => null,
            ],
            self::STATUS_READY => [
                'label' => Craft::t('secret-santa', 'Ready'),
                'color' => Color::Green,
                'icon'  => null,
            ],
            self::STATUS_DRAWN => [
                'label' => Craft::t('secret-santa', 'Drawn'),
                'color' => Color::Blue,
                'icon'  => null,
            ],
        ];
    }

    public function getStatusBadgeConfig(): array
    {
        $badges = self::statusBadges();

        $config = $badges[$this->groupStatus] ?? [
            'label' => Craft::t('secret-santa', 'Unknown'),
            'color' => Color::Gray,
        ];

        return $config;
    }

    public function getStatus(): ?string
    {
        return $this->groupStatus;
    }

    public function canView(User $user): bool
    {
        return true;
    }

    public function canSave(User $user): bool
    {
        return true;
    }

    public function canDelete(User $user): bool
    {
        return true;
    }

    public function canModify(): bool
    {
        return SecretSanta::$plugin
            ->getGroupGuard()
            ->canModify($this);
    }

    public function canDraw(): bool
    {
        return SecretSanta::$plugin->groupGuard->canDraw($this);
    }

    public function canResendDraw(): bool
    {
        return in_array($this->groupStatus, ['drawn', 'completed'], true);
    }

    public static function find(): ElementQueryInterface
    {
        return new SantaGroupQuery(static::class);
    }

    public function isDrawn(): bool
    {
        return $this->groupStatus === self::STATUS_DRAWN;
    }

    public function afterSave(bool $isNew): void
    {
        if ($isNew) {
            Craft::$app->db->createCommand()
                ->insert('{{%santa_groups}}', [
                    'id' => $this->id,
                    'title' => $this->title,
                    'enabled' => $this->enabled,
                    'groupStatus' => $this->groupStatus ?? 'draft',
                    'dateCreated' => Db::prepareDateForDb($this->dateCreated),
                    'dateUpdated' => Db::prepareDateForDb($this->dateUpdated),
                    'uid' => $this->uid,
                ])
                ->execute();
        } else {
            Craft::$app->db->createCommand()
                ->update('{{%santa_groups}}', [
                    'title' => $this->title,
                    'enabled' => $this->enabled,
                    'groupStatus' => $this->groupStatus ?? 'draft',
                    'dateUpdated' => Db::prepareDateForDb($this->dateUpdated),
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);

    }

    public function afterPopulate(): void
    {
        parent::afterPopulate();

        if ($this->groupStatus === null) {
            $this->groupStatus = 'draft';
        }
    }


    /* ================== CP ================== */

    public static function sources(string $context): array
    {
        return [
            [
                'key' => '*',
                'label' => Craft::t('secret-santa', 'All groups'),
                'criteria' => [],
                'defaultSort' => ['title', 'asc'],
            ],
        ];
    }

    /* HELPERS */


    public function allMembersAccepted(): bool
    {
        // Fetch all members of the group
        $members = SantaMemberRecord::find()
            ->where(['groupId' => $this->id])
            ->all();

        // No members? Definitely not accepted.
        if (!$members) {
            return false;
        }

        foreach ($members as $member) {
            // Adjust the field name if yours differs
            if (!$member->dateAccepted) {
                return false;
            }
        }

        return true;
    }


    public function getMembersCount(): int
    {
        return SantaMemberRecord::find()
            ->where(['groupId' => $this->id])
            ->count();
    }

    // public function getAttributeHtml(string $attribute): string
    // {
    //     if ($attribute === 'status') {
    //         $status = $this->getStatus();

    //         return Cp::statusLabelHtml([
    //             'label' => static::statuses()[$status] ?? $status,
    //             'color' => match ($status) {
    //                 'ready' => 'teal',
    //                 'drawn' => 'red',
    //                 'draft' => 'orange',
    //                 default => 'gray',
    //             },
    //         ]);
    //     }

    //     return parent::getAttributeHtml($attribute);
    // }

    public function getAttributeHtml(string $attribute): string
    {
        if ($attribute === 'groupStatus') {
            return Cp::statusLabelHtml($this->getStatusBadgeConfig());
        }

        return parent::getAttributeHtml($attribute);
    }

    // public function getTableAttributeHtml(string $attribute): string
    // {
    //     return match ($attribute) {
    //         'membersCount' => (string)$this->getMembersCount(),
    //         'status' => $this->getStatusHtml(),
    //         default => parent::getTableAttributeHtml($attribute),
    //     };
    // }

    public function getTableAttributeHtml(string $attribute): string
    {
        return match ($attribute) {
            'membersCount' => (string)$this->getMembersCount(),
            'groupStatus' => Cp::statusLabelHtml($this->getStatusBadgeConfig()),
            default => parent::getTableAttributeHtml($attribute),
        };
    }


    /* PROTECTED STUFF */

    protected function defineAttributes(): array
    {
        return array_merge(parent::defineAttributes(), [
            'groupStatus' => 'string',
        ]);
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'membersCount' => ['label' => Craft::t('secret-santa', 'Members')],
            'groupStatus' => ['label' => Craft::t('secret-santa', 'Status')],
            'dateCreated' => ['label' => Craft::t('app', 'Date Created')],
        ];
    }

    // protected static function defineAvailableTableAttributes(): array
    // {
    //     return [
    //         'membersCount' => Craft::t('secret-santa', 'Members'),
    //         'groupStatus' => Craft::t('secret-santa', 'Status'),
    //         'status' => Craft::t('app', 'Date Created'),
    //     ];
    // }

    protected static function defineAvailableTableAttributes(): array
    {
        return [
            'membersCount' => Craft::t('secret-santa', 'Members'),
            'groupStatus' => Craft::t('secret-santa', 'Status'),
            'dateCreated' => Craft::t('app', 'Date Created'),
        ];
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        return ['membersCount', 'groupStatus', 'dateCreated'];
    }

    // protected function getGroupStatusHtml(): string
    // {
    //     $status = $this->groupStatus ?? 'draft';

    //     return Cp::statusLabelHtml(
    //         match ($status) {
    //             'ready' => 'green',
    //             'drawn' => 'orange',
    //             'completed' => 'blue',
    //             default => 'gray',
    //         },
    //         Craft::t('secret-santa', ucfirst($status))
    //     );
    // }

    protected function cpEditUrl(): ?string
    {
        return UrlHelper::cpUrl('secret-santa/groups/' . $this->id);
    }


}

