<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * OutboxMessage model
 *
 * @property int $id
 * @property string $aggregate_id
 * @property string $aggregate_type
 * @property string $event_type
 * @property string $payload
 * @property string $status
 * @property int $attempts
 * @property int $max_attempts
 * @property string $error_message
 * @property int $created_at
 * @property int $updated_at
 * @property int $processed_at
 */
class OutboxMessage extends ActiveRecord
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%outbox_messages}}';
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
            [['aggregate_id', 'aggregate_type', 'event_type', 'payload'], 'required'],
            [['payload', 'error_message'], 'string'],
            [['attempts', 'max_attempts', 'created_at', 'updated_at', 'processed_at'], 'integer'],
            [['aggregate_id', 'aggregate_type', 'event_type'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 50],
            [['status'], 'in', 'range' => [
                self::STATUS_PENDING,
                self::STATUS_PROCESSING,
                self::STATUS_COMPLETED,
                self::STATUS_FAILED,
            ]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aggregate_id' => 'Aggregate ID',
            'aggregate_type' => 'Aggregate Type',
            'event_type' => 'Event Type',
            'payload' => 'Payload',
            'status' => 'Status',
            'attempts' => 'Attempts',
            'max_attempts' => 'Max Attempts',
            'error_message' => 'Error Message',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'processed_at' => 'Processed At',
        ];
    }

    /**
     * Get payload as array
     */
    public function getPayloadArray(): array
    {
        return json_decode($this->payload, true) ?: [];
    }

    /**
     * Set payload from array
     */
    public function setPayloadArray(array $payload): void
    {
        $this->payload = json_encode($payload);
    }

    /**
     * Check if can retry
     */
    public function canRetry(): bool
    {
        return $this->attempts < $this->max_attempts;
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing(): bool
    {
        $this->status = self::STATUS_PROCESSING;
        $this->attempts++;
        $this->updated_at = time();
        return $this->save(false);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): bool
    {
        $this->status = self::STATUS_COMPLETED;
        $this->processed_at = time();
        $this->updated_at = time();
        return $this->save(false);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $errorMessage): bool
    {
        $this->status = self::STATUS_FAILED;
        $this->error_message = $errorMessage;
        $this->updated_at = time();
        return $this->save(false);
    }
}
