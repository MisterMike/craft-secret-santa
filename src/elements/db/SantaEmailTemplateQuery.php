<?php

/*
 * Use like SantaEmailTemplateElement::find()->handle('invitation')->enabled(true)->one();
*/

namespace nibiru\secretsanta\elements\db;

use craft\elements\db\ElementQuery;

class SantaEmailTemplateQuery extends ElementQuery
{
    public ?string $handle = null;

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('santa_email_templates');

        $this->query->select([
            'santa_email_templates.handle',
            'santa_email_templates.subject',
            'santa_email_templates.mjmlBody',
            'santa_email_templates.enabled',
        ]);

        if ($this->handle) {
            $this->subQuery->andWhere([
                'santa_email_templates.handle' => $this->handle,
            ]);
        }

        return parent::beforePrepare();
    }

    public function handle(string $value): static
    {
        $this->handle = $value;
        return $this;
    }
}
