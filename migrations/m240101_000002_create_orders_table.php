<?php

use yii\db\Migration;

/**
 * Handles the creation of table `orders`.
 */
class m240101_000002_create_orders_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%orders}}', [
            'id' => $this->primaryKey(),
            'order_number' => $this->string(100)->notNull()->unique(),
            'user_id' => $this->integer()->notNull(),
            'status' => $this->string(50)->notNull()->defaultValue('pending'),
            'total_amount' => $this->decimal(10, 2)->notNull(),
            'items' => $this->text()->notNull(),
            'metadata' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Create indexes
        $this->createIndex(
            'idx-orders-order_number',
            '{{%orders}}',
            'order_number'
        );

        $this->createIndex(
            'idx-orders-user_id',
            '{{%orders}}',
            'user_id'
        );

        $this->createIndex(
            'idx-orders-status',
            '{{%orders}}',
            'status'
        );

        $this->createIndex(
            'idx-orders-created_at',
            '{{%orders}}',
            'created_at'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%orders}}');
    }
}
