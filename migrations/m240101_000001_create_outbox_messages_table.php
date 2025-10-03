<?php

use yii\db\Migration;

/**
 * Handles the creation of table `outbox_messages`.
 */
class m240101_000001_create_outbox_messages_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%outbox_messages}}', [
            'id' => $this->primaryKey(),
            'aggregate_id' => $this->string(255)->notNull(),
            'aggregate_type' => $this->string(255)->notNull(),
            'event_type' => $this->string(255)->notNull(),
            'payload' => $this->text()->notNull(),
            'status' => $this->string(50)->notNull()->defaultValue('pending'),
            'attempts' => $this->integer()->notNull()->defaultValue(0),
            'max_attempts' => $this->integer()->notNull()->defaultValue(3),
            'error_message' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'processed_at' => $this->integer(),
        ]);

        // Create indexes
        $this->createIndex(
            'idx-outbox_messages-status',
            '{{%outbox_messages}}',
            'status'
        );

        $this->createIndex(
            'idx-outbox_messages-created_at',
            '{{%outbox_messages}}',
            'created_at'
        );

        $this->createIndex(
            'idx-outbox_messages-aggregate',
            '{{%outbox_messages}}',
            ['aggregate_type', 'aggregate_id']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%outbox_messages}}');
    }
}
