<?php
namespace nibiru\secretsanta\models;

use Craft;
use craft\base\Model;
use craft\elements\User as CraftUser;

class SantaMember extends Model
{

    public ?int $id = null;
    public string $uid;
    public int $userId;
    public int $groupId;

    public ?string $token           = null;
    public ?string $wishlist        = null;

    public ?\DateTime $dateCreated  = null;
    public ?\DateTime $dateUpdated  = null;
    public ?\DateTime $dateInvited  = null;
    public ?\DateTime $dateAccepted = null;
    public ?\DateTime $dateDrawn    = null;

    public bool $wishlistRefused    = false;

    public int $drawnMemberId;

    private ?CraftUser $_user       = null;

    public function getUser(): ?CraftUser
    {
        if ($this->_user === null) {
            $this->_user = Craft::$app->users->getUserById($this->userId);
        }

        return $this->_user;
    }

    public function getIsReadyForDraw(): bool
    {
        if (!$this->dateAccepted) {
            return false;
        }

        if ($this->wishlistRefused) {
            return true;
        }

        return !empty(trim((string)$this->wishlist));
    }


    public function rules(): array
    {
        return [
            [['groupId', 'userId'], 'required'],
            [['groupId', 'userId'], 'integer'],
            [['wishlist'], 'string'],
            [['wishlistRefused'], 'boolean'],
            [['dateInvited', 'dateAccepted'], 'safe'],
        ];
    }
}
