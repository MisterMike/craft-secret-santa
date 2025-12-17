<?php
namespace nibiru\secretsanta\records;

use craft\db\ActiveRecord;

class SantaGroupRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%santa_groups}}';
    }
}
