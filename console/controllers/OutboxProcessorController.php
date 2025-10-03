<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\models\OutboxMessage;

/**
 * Outbox Processor Controller
 * Processes pending outbox messages and publishes to RabbitMQ
 */
class OutboxProcessorController extends Controller
{
    /**
     * Run outbox processor
     */
    public function actionRun()
    {
        echo "Starting Outbox Processor...\n";

        while (true) {
            try {
                $this->processMessages();
                sleep(1); // Wait 1 second between batches
            } catch (\Exception $e) {
                Yii::error("Outbox processor error: " . $e->getMessage(), __METHOD__);
                sleep(5); // Wait longer on error
            }
        }
    }

    /**
     * Process pending messages
     */
    private function processMessages(): void
    {
        $messages = OutboxMessage::find()
            ->where(['status' => OutboxMessage::STATUS_PENDING])
            ->orWhere([
                'and',
                ['status' => OutboxMessage::STATUS_FAILED],
                ['<', 'attempts', new \yii\db\Expression('max_attempts')]
            ])
            ->limit(10)
            ->all();

        foreach ($messages as $message) {
            $this->processMessage($message);
        }
    }

    /**
     * Process single message
     */
    private function processMessage(OutboxMessage $message): void
    {
        echo "Processing message #{$message->id} ({$message->event_type})...\n";

        $message->markAsProcessing();

        try {
            // Publish to RabbitMQ
            $queue = $this->getQueueName($message->event_type);

            Yii::$app->rabbitmq->declareQueue($queue);
            Yii::$app->rabbitmq->publish($queue, $message->payload, [
                'correlation_id' => $message->id,
                'timestamp' => time(),
            ]);

            $message->markAsCompleted();
            echo "Message #{$message->id} published successfully\n";

        } catch (\Exception $e) {
            Yii::error("Failed to publish message #{$message->id}: " . $e->getMessage(), __METHOD__);

            if (!$message->canRetry()) {
                $message->markAsFailed($e->getMessage());
                echo "Message #{$message->id} failed permanently\n";
            } else {
                $message->markAsFailed($e->getMessage());
                echo "Message #{$message->id} will be retried\n";
            }
        }
    }

    /**
     * Get queue name for event type
     */
    private function getQueueName(string $eventType): string
    {
        return 'events.' . strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $eventType));
    }
}
