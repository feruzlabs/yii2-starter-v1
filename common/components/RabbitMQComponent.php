<?php

namespace common\components;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * RabbitMQ Component
 * Handles connection and basic operations with RabbitMQ
 */
class RabbitMQComponent extends Component
{
    public string $host = 'rabbitmq';
    public int $port = 5672;
    public string $user = 'guest';
    public string $password = 'guest';
    public string $vhost = '/';

    private ?AMQPStreamConnection $connection = null;
    private array $channels = [];

    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        if (empty($this->host)) {
            throw new InvalidConfigException('RabbitMQ host is required');
        }
    }

    /**
     * Get connection to RabbitMQ
     */
    public function getConnection(): AMQPStreamConnection
    {
        if ($this->connection === null || !$this->connection->isConnected()) {
            $this->connection = new AMQPStreamConnection(
                $this->host,
                $this->port,
                $this->user,
                $this->password,
                $this->vhost
            );
        }

        return $this->connection;
    }

    /**
     * Get channel
     */
    public function getChannel(int $channelId = null): \PhpAmqpLib\Channel\AMQPChannel
    {
        if ($channelId === null) {
            return $this->getConnection()->channel();
        }

        if (!isset($this->channels[$channelId])) {
            $this->channels[$channelId] = $this->getConnection()->channel($channelId);
        }

        return $this->channels[$channelId];
    }

    /**
     * Declare queue
     */
    public function declareQueue(
        string $queue,
        bool $passive = false,
        bool $durable = true,
        bool $exclusive = false,
        bool $autoDelete = false
    ): void {
        $channel = $this->getChannel();
        $channel->queue_declare($queue, $passive, $durable, $exclusive, $autoDelete);
    }

    /**
     * Publish message to queue
     */
    public function publish(string $queue, string $message, array $properties = []): void
    {
        $channel = $this->getChannel();

        $msg = new AMQPMessage($message, array_merge([
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            'content_type' => 'application/json',
        ], $properties));

        $channel->basic_publish($msg, '', $queue);
    }

    /**
     * Consume messages from queue
     */
    public function consume(string $queue, callable $callback, string $consumerTag = ''): void
    {
        $channel = $this->getChannel();

        $channel->basic_consume(
            $queue,
            $consumerTag,
            false,
            false,
            false,
            false,
            $callback
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }

    /**
     * Close connection
     */
    public function close(): void
    {
        foreach ($this->channels as $channel) {
            $channel->close();
        }

        if ($this->connection !== null && $this->connection->isConnected()) {
            $this->connection->close();
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
    }
}
