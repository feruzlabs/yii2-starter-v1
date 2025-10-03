<?php

namespace common\components;

use Yii;
use yii\base\Component;

/**
 * PHP-FPM Monitor
 * Monitors PHP-FPM status and health
 */
class PhpFpmMonitor extends Component
{
    public string $statusUrl = 'http://php-fpm:9001/fpm-status?json';
    public string $pingUrl = 'http://php-fpm:9001/fpm-ping';

    private string $redisKey = 'fpm:status';

    /**
     * Get FPM status
     */
    public function getStatus(): ?array
    {
        try {
            $ch = curl_init($this->statusUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                return null;
            }

            $status = json_decode($response, true);

            // Cache the status
            Yii::$app->redis->setex($this->redisKey, 10, json_encode($status));

            return $status;
        } catch (\Exception $e) {
            Yii::error("Failed to get FPM status: " . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    /**
     * Get cached FPM status
     */
    public function getCachedStatus(): ?array
    {
        $cached = Yii::$app->redis->get($this->redisKey);
        return $cached ? json_decode($cached, true) : $this->getStatus();
    }

    /**
     * Check if FPM is healthy
     */
    public function isHealthy(): bool
    {
        try {
            $ch = curl_init($this->pingUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $httpCode === 200 && trim($response) === 'pong';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get FPM metrics
     */
    public function getMetrics(): array
    {
        $status = $this->getCachedStatus();

        if (!$status) {
            return [
                'healthy' => false,
                'error' => 'Unable to fetch FPM status',
            ];
        }

        $pool = $status['pool'] ?? 'unknown';
        $processManager = $status['process-manager'] ?? 'dynamic';

        $activeProcesses = $status['active-processes'] ?? 0;
        $totalProcesses = $status['total-processes'] ?? 0;
        $maxActiveProcesses = $status['max-active-processes'] ?? 0;
        $maxChildren = $status['max-children-reached'] ?? 0;

        $idleProcesses = $status['idle-processes'] ?? 0;
        $listenQueue = $status['listen-queue'] ?? 0;
        $maxListenQueue = $status['max-listen-queue'] ?? 0;

        // Calculate utilization
        $utilization = $totalProcesses > 0 ? ($activeProcesses / $totalProcesses) * 100 : 0;

        // Determine health status
        $warnings = [];

        if ($listenQueue > 0) {
            $warnings[] = "Listen queue has {$listenQueue} items";
        }

        if ($maxChildren > 0) {
            $warnings[] = "Max children reached {$maxChildren} times";
        }

        if ($utilization > 80) {
            $warnings[] = "High utilization: {$utilization}%";
        }

        return [
            'healthy' => empty($warnings),
            'pool' => $pool,
            'process_manager' => $processManager,
            'active_processes' => $activeProcesses,
            'total_processes' => $totalProcesses,
            'idle_processes' => $idleProcesses,
            'max_active_processes' => $maxActiveProcesses,
            'listen_queue' => $listenQueue,
            'max_listen_queue' => $maxListenQueue,
            'max_children_reached' => $maxChildren,
            'utilization_percent' => round($utilization, 2),
            'warnings' => $warnings,
            'timestamp' => time(),
        ];
    }

    /**
     * Record metrics
     */
    public function recordMetrics(): void
    {
        $metrics = $this->getMetrics();

        if ($metrics && Yii::$app->has('metricsCollector')) {
            Yii::$app->metricsCollector->record('fpm', $metrics);
        }
    }

    /**
     * Check and alert if needed
     */
    public function checkAndAlert(): void
    {
        $metrics = $this->getMetrics();

        if (!$metrics['healthy'] && !empty($metrics['warnings'])) {
            $message = "PHP-FPM Health Warning:\n" . implode("\n", $metrics['warnings']);

            if (Yii::$app->has('alertManager')) {
                Yii::$app->alertManager->sendAlert('fpm_warning', $message, $metrics);
            }

            Yii::warning($message, __METHOD__);
        }
    }
}
