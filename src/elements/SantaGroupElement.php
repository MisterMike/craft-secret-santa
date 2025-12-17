<?php

namespace nibiru\secretsanta\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\helpers\Db;
use craft\helpers\UrlHelper;
use nibiru\secretsanta\elements\db\SantaGroupQuery;

class SantaGroupElement extends Element
{
    public ?string $title = '';
    public bool $enabled = true;

    /* ================== Meta ================== */

    public static function displayName(): string
    {
        return Craft::t('secret-santa', 'Secret Santa Group');
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

    public function canView(User $user): bool
    {
        return true;
    }

    public function canSave(User $user): bool
    {
        return true;
    }

    public static function find(): ElementQueryInterface
    {
        return new SantaGroupQuery(static::class);
    }

    protected function cpEditUrl(): ?string
    {
        return UrlHelper::cpUrl('secret-santa/groups/' . $this->id);
    }

    public function afterSave(bool $isNew): void
    {
        if ($isNew) {
            Craft::$app->db->createCommand()
                ->insert('{{%santa_groups}}', [
                    'id' => $this->id,
                    'title' => $this->title,
                    'enabled' => $this->enabled,
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
                    'dateUpdated' => Db::prepareDateForDb($this->dateUpdated),
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);

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

    protected static function defineTableAttributes(): array
    {
        return [
            'title' => ['label' => Craft::t('app', 'Title')],
            'dateCreated' => ['label' => Craft::t('app', 'Date Created')],
        ];
    }

    protected static function defineAvailableTableAttributes(): array
    {
        return [
            'title' => Craft::t('app', 'Title3'),
            'dateCreated' => Craft::t('app', 'Date Created'),
        ];
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        return ['title', 'dateCreated'];
    }
}

