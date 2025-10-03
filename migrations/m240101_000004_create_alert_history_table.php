<?php

use yii\db\Migration;

/**
 * Handles the creation of table `alert_history`.
 */
class m240101_000004_create_alert_history_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%alert_history}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string(255)->notNull(),
            'severity' => $this->string(50)->notNull(),
            'message' => $this->text()->notNull(),
            'context' => $this->text(),
            'channels' => $this->string(255),
            'sent_at' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);

        // Create indexes
        $this->createIndex(
            'idx-alert_history-type',
            '{{%alert_history}}',
            'type'
        );

        $this->createIndex(
            'idx-alert_history-severity',
            '{{%alert_history}}',
            'severity'
        );

        $this->createIndex(
            'idx-alert_history-created_at',
            '{{%alert_history}}',
            'created_at'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%alert_history}}');
    }
}
