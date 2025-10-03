<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Order model
 *
 * @property int $id
 * @property string $order_number
 * @property int $user_id
 * @property string $status
 * @property float $total_amount
 * @property string $items
 * @property string $metadata
 * @property int $created_at
 * @property int $updated_at
 */
class Order extends ActiveRecord
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%orders}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_number', 'user_id', 'total_amount', 'items'], 'required'],
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['total_amount'], 'number'],
            [['items', 'metadata'], 'string'],
            [['order_number'], 'string', 'max' => 100],
            [['status'], 'string', 'max' => 50],
            [['status'], 'in', 'range' => [
                self::STATUS_PENDING,
                self::STATUS_PROCESSING,
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED,
            ]],
            [['order_number'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_number' => 'Order Number',
            'user_id' => 'User ID',
            'status' => 'Status',
            'total_amount' => 'Total Amount',
            'items' => 'Items',
            'metadata' => 'Metadata',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Get items as array
     */
    public function getItemsArray(): array
    {
        return json_decode($this->items, true) ?: [];
    }

    /**
     * Set items from array
     */
    public function setItemsArray(array $items): void
    {
        $this->items = json_encode($items);
    }

    /**
     * Get metadata as array
     */
    public function getMetadataArray(): array
    {
        return json_decode($this->metadata, true) ?: [];
    }

    /**
     * Set metadata from array
     */
    public function setMetadataArray(array $metadata): void
    {
        $this->metadata = json_encode($metadata);
    }
}
