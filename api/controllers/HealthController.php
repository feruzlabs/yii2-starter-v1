<?php

namespace api\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;

/**
 * Health Check Controller
 */
class HealthController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * Health check endpoint
     */
    public function actionIndex(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'rabbitmq' => $this->checkRabbitMQ(),
        ];

        $healthy = !in_array(false, $checks, true);

        return [
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'timestamp' => date('c'),
            'checks' => $checks,
        ];
    }

    /**
     * Check database connection
     */
    private function checkDatabase(): bool
    {
        try {
            Yii::$app->db->createCommand('SELECT 1')->queryOne();
            return true;
        } catch (\Exception $e) {
            Yii::error("Database health check failed: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Check Redis connection
     */
    private function checkRedis(): bool
    {
        try {
            Yii::$app->redis->ping();
            return true;
        } catch (\Exception $e) {
            Yii::error("Redis health check failed: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Check RabbitMQ connection
     */
    private function checkRabbitMQ(): bool
    {
        try {
            $connection = Yii::$app->rabbitmq->getConnection();
            return $connection->isConnected();
        } catch (\Exception $e) {
            Yii::error("RabbitMQ health check failed: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}
