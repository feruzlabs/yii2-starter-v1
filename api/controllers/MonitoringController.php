<?php

namespace api\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;

/**
 * Monitoring Controller
 */
class MonitoringController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * Get application metrics
     */
    public function actionMetrics(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $metricsCollector = Yii::$app->metricsCollector;

        return [
            'summary' => $metricsCollector->getSummary(60),
            'fpm' => $this->getFpmMetrics(),
            'circuit_breakers' => $this->getCircuitBreakerMetrics(),
            'rate_limits' => $this->getRateLimitMetrics(),
            'timestamp' => time(),
        ];
    }

    /**
     * Get FPM status
     */
    public function actionFpmStatus(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $fpmMonitor = Yii::$app->fpmMonitor;

        return $fpmMonitor->getMetrics();
    }

    /**
     * Get system status
     */
    public function actionStatus(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'throttler' => Yii::$app->throttler->getState(),
            'fpm' => Yii::$app->fpmMonitor->getMetrics(),
            'alerts' => Yii::$app->alertManager->getHistory(24),
        ];
    }

    /**
     * Get FPM metrics
     */
    private function getFpmMetrics(): array
    {
        if (!Yii::$app->has('fpmMonitor')) {
            return [];
        }

        return Yii::$app->fpmMonitor->getMetrics();
    }

    /**
     * Get circuit breaker metrics
     */
    private function getCircuitBreakerMetrics(): array
    {
        // This would typically iterate through known services
        // For now, return empty array
        return [];
    }

    /**
     * Get rate limit metrics
     */
    private function getRateLimitMetrics(): array
    {
        // This would return rate limit statistics
        // For now, return empty array
        return [];
    }
}
