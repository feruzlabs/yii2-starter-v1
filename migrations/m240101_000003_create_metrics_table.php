<?php

use yii\db\Migration;

/**
 * Handles the creation of table `metrics`.
 */
class m240101_000003_create_metrics_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%metrics}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'value' => $this->decimal(20, 6)->notNull(),
            'labels' => $this->text(),
            'timestamp' => $this->bigInteger()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);

        // Create indexes
        $this->createIndex(
            'idx-metrics-name',
            '{{%metrics}}',
            'name'
        );

        $this->createIndex(
            'idx-metrics-timestamp',
            '{{%metrics}}',
            'timestamp'
        );

        $this->createIndex(
            'idx-metrics-name-timestamp',
            '{{%metrics}}',
            ['name', 'timestamp']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%metrics}}');
    }
}
