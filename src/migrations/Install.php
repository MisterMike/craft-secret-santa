<?php
namespace nibiru\secretsanta\migrations;

use Craft;
use craft\base\Element;
use craft\db\Migration;

use nibiru\secretsanta\elements\SantaGroupElement;

class Install extends Migration
{
    public function safeUp(): bool
    {

        $db = Craft::$app->getDb();
        
        // Secret Santa Groups (Element table)
        if (!$db->tableExists('{{%santa_groups}}')) {
            $this->createTable('{{%santa_groups}}', [
                'id'            => $this->integer()->notNull(),
                'title'         => $this->string()->notNull(),
                'enabled'       => $this->boolean()->defaultValue(true),
                'groupStatus'   => $this->string(20)->notNull()->defaultValue('draft'),
                'dateCreated'   => $this->dateTime()->notNull(),
                'dateUpdated'   => $this->dateTime()->notNull(),
                'uid'           => $this->uid(),
            ]);
        }


        // Members table
        if (!$db->tableExists('{{%santa_members}}')) {
            $this->createTable('{{%santa_members}}', [
                'id'            => $this->primaryKey(),
                'groupId'       => $this->integer()->notNull(),
                'userId'        => $this->integer()->notNull(),
                'dateInvited'   => $this->dateTime()->null(),
                'dateAccepted'  => $this->dateTime()->null(),
                'dateDrawn'     => $this->dateTime()->null(),
                'wishlist'      => $this->longText(),
                'wishlistRefused' => $this->boolean()->defaultValue(false),

                // Draw result (references santa_members.id)
                'drawnMemberId' => $this->integer()->null(),

                // Invite / accept token
                'token'         => $this->string(64)->notNull(),

                // Craft standard columns
                'dateCreated'   => $this->dateTime()->notNull(),
                'dateUpdated'   => $this->dateTime()->notNull(),
                'uid'           => $this->uid(),
            ]);
        }

        // Santa Email Templates
        if (!$db->tableExists('{{%santa_email_templates}}')) {
            $this->createTable('{{%santa_email_templates}}', [
                'id'            => $this->primaryKey(),
                'title'         => $this->string()->notNull(),
                'handle'        => $this->string(64)->notNull(),
                'subject'       => $this->string()->notNull(),
                'mjmlBody'      => $this->text()->notNull(),
                'enabled'       => $this->boolean()->notNull()->defaultValue(true),

                // Craft standard columns
                'dateCreated'   => $this->dateTime()->notNull(),
                'dateUpdated'   => $this->dateTime()->notNull(),
                'uid'           => $this->uid(),
            ]);

            // Unique handle (important!)
            $this->createIndex(
                null,
                '{{%santa_email_templates}}',
                ['handle'],
                true
            );
        }

        // Make `id` the primary key
        $this->addPrimaryKey(null, '{{%santa_groups}}', 'id');

        // Link to Craft elements table
        $this->addForeignKey( null, '{{%santa_groups}}', 'id', '{{%elements}}', 'id', 'CASCADE');

        $this->addForeignKey(null, '{{%santa_members}}', 'groupId', '{{%santa_groups}}', 'id', 'CASCADE');
        $this->addForeignKey(null, '{{%santa_members}}', 'userId', '{{%users}}', 'id', 'CASCADE');

        return true;
    }

    public function safeDown(): bool
    {
        
        $this->deleteElements();
        $this->deleteTables();
        return true;
    }

    /**
     * Deletes elements.
     */
    protected function deleteElements(): void
    {
        $elementTypes = [
            SantaGroupElement::class
        ];

        $elementsService = Craft::$app->getElements();

        foreach ($elementTypes as $elementType) {
            /** @var Element $elementType */
            $elements = $elementType::findAll();

            foreach ($elements as $element) {
                // Hard delete elements
                $elementsService->deleteElement($element, true);
            }
        }
    }

    /**
     * Deletes tables.
     */
    protected function deleteTables(): void
    {
        // Drop tables with foreign keys first
        $this->dropTableIfExists('{{%santa_members}}');
        $this->dropTableIfExists('{{%santa_groups}}');
    }
}
