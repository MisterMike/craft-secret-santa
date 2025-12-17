<?php
namespace nibiru\secretsanta\services;

use Craft;
use craft\base\Component;

use nibiru\secretsanta\elements\SantaGroupElement;
use nibiru\secretsanta\records\SantaGroupRecord;

class GroupService extends Component
{

    public function getGroupElementById(int $id): ?SantaGroupElement
    {
        return SantaGroupElement::find()
            ->id($id)
            ->one();
    }

    public function getAll()
    {
        return SantaGroupRecord::find()->all();
    }

    public function getById(int $id)
    {
        return SantaGroupRecord::findOne($id);
    }

    public function create(string $title)
    {
        $rec = new SantaGroupRecord();
        $rec->title = $title;
        $rec->save();
        return $rec;
    }
}
