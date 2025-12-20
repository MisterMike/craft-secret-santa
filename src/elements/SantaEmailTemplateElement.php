<?php

namespace nibiru\secretsanta\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\helpers\Db;
use craft\helpers\UrlHelper;

use nibiru\secretsanta\elements\db\SantaEmailTemplateQuery;

class SantaEmailTemplateElement extends Element
{
    public ?string $title = '';
    public string $handle = '';
    public string $subject = '';
    public string $mjmlBody = '';
    public bool $enabled = true;

    /* ================== Meta ================== */

    public static function displayName(): string
    {
        return Craft::t('secret-santa', 'Email Template');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('secret-santa', 'Email Templates');
    }

    public static function tableName(): string
    {
        return '{{%santa_email_templates}}';
    }

    public static function hasTitles(): bool
    {
        return true;
    }

    public static function hasUris(): bool
    {
        return false;
    }

    public static function hasContent(): bool
    {
        return false;
    }

    public static function isLocalized(): bool
    {
        return false;
    }


    /* ================== Permissions ================== */

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

    /* ================== Queries ================== */

    public static function find(): ElementQueryInterface
    {
        return new SantaEmailTemplateQuery(static::class);
    }

    /* ================== Persistence ================== */

    public function afterSave(bool $isNew): void
    {
        $data = [
            'title' => $this->title,
            'handle' => $this->handle,
            'subject' => $this->subject,
            'mjmlBody' => $this->mjmlBody,
            'enabled' => $this->enabled,
            'dateUpdated' => Db::prepareDateForDb($this->dateUpdated),
        ];

        if ($isNew) {
            $data['id'] = $this->id;
            $data['dateCreated'] = Db::prepareDateForDb($this->dateCreated);
            $data['uid'] = $this->uid;

            Craft::$app->db->createCommand()
                ->insert(self::tableName(), $data)
                ->execute();
        } else {
            Craft::$app->db->createCommand()
                ->update(
                    self::tableName(),
                    $data,
                    ['id' => $this->id]
                )
                ->execute();
        }

        parent::afterSave($isNew);
    }

    public function afterPopulate(): void
    {
        parent::afterPopulate();

        $this->enabled ??= true;
    }

    public static function sources(string $context): array
    {
        return [
            [
                'key' => '*',
                'label' => Craft::t('secret-santa', 'All email templates'),
                'criteria' => [],
                'defaultSort' => ['title', 'asc'],
            ],
        ];
    }

    /* ================== CP ================== */

    protected function cpEditUrl(): ?string
    {
        return UrlHelper::cpUrl('secret-santa/emails/' . $this->id);
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'title' => ['label' => Craft::t('app', 'Title')],
            'handle' => ['label' => Craft::t('secret-santa', 'Handle')],
            'enabled' => ['label' => Craft::t('app', 'Enabled')],
            'dateUpdated' => ['label' => Craft::t('app', 'Date Updated')],
        ];
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        return ['title', 'handle', 'enabled'];
    }

    protected static function defineAttributes(): array
    {
        return array_merge(parent::defineAttributes(), [
            'handle' => 'string',
            'subject' => 'string',
            'mjmlBody' => 'string',
            'enabled' => 'bool',
        ]);
    }

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            [['handle'], 'string', 'max' => 64],
            [['title', 'subject', 'mjmlBody'], 'string'],
    ]);
    }

}
