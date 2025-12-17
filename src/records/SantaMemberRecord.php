<?php
namespace nibiru\secretsanta\records;

use craft\db\ActiveRecord;

class SantaMemberRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%santa_members}}';
    }
}
