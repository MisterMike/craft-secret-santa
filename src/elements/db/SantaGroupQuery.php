<?php

namespace nibiru\secretsanta\elements\db;

use craft\elements\db\ElementQuery;

class SantaGroupQuery extends ElementQuery
{
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('santa_groups');

        $this->query->select([
            'santa_groups.title',
            'santa_groups.enabled',
            'santa_groups.groupStatus',
        ]);

        return parent::beforePrepare();
    }
}
