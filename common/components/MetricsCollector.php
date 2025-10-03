<?php

namespace common\components;

use Yii;
use yii\base\Component;

/**
 * Metrics Collector
 * Collects and stores application metrics
 */
class MetricsCollector extends Component
{
    private string $redisPrefix = 'metrics:';
    private int $retentionDays = 7;

    /**
     * Record a metric
     */
    public function record(string $name, array $data = []): void
    {
        $redis = Yii::$app->redis;
        $timestamp = microtime(true);

        $metric = [
            'name' => $name,
            'timestamp' => $timestamp,
            'data' => $data,
        ];

        // Store in time-series sorted set
        $key = $this->redisPrefix . $name . ':' . date('Y-m-d');
        $redis->zadd($key, $timestamp, json_encode($metric));

        // Set expiry
        $redis->expire($key, $this->retentionDays * 86400);

        // Update current value
        $currentKey = $this->redisPrefix . 'current:' . $name;
        $redis->setex($currentKey, 3600, json_encode($metric));
    }

    /**
     * Increment a counter
     */
    public function increment(string $name, int $value = 1): void
    {
        $redis = Yii::$app->redis;
        $key = $this->redisPrefix . 'counter:' . $name;
        $redis->incrby($key, $value);
        $redis->expire($key, $this->retentionDays * 86400);
    }

    /**
     * Get metrics for a time range
     */
    public function getMetrics(string $name, int $fromTimestamp = null, int $toTimestamp = null): array
    {
        $redis = Yii::$app->redis;
        $fromTimestamp = $fromTimestamp ?? (time() - 3600); // Last hour by default
        $toTimestamp = $toTimestamp ?? time();

        $metrics = [];

        // Get all days in range
        $currentDate = date('Y-m-d', $fromTimestamp);
        $endDate = date('Y-m-d', $toTimestamp);

        while ($currentDate <= $endDate) {
            $key = $this->redisPrefix . $name . ':' . $currentDate;
            $dayMetrics = $redis->zrangebyscore($key, $fromTimestamp, $toTimestamp);

            foreach ($dayMetrics as $metric) {
                $metrics[] = json_decode($metric, true);
            }

            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }

        return $metrics;
    }

    /**
     * Get current metric value
     */
    public function getCurrent(string $name)
    {
        $redis = Yii::$app->redis;
        $key = $this->redisPrefix . 'current:' . $name;
        $data = $redis->get($key);

        return $data ? json_decode($data, true) : null;
    }

    /**
     * Get counter value
     */
    public function getCounter(string $name): int
    {
        $redis = Yii::$app->redis;
        $key = $this->redisPrefix . 'counter:' . $name;
        return (int)$redis->get($key);
    }

    /**
     * Record request metrics
     */
    public function recordRequest(float $duration, int $statusCode, string $endpoint): void
    {
        $this->record('request', [
            'duration' => $duration,
            'status_code' => $statusCode,
            'endpoint' => $endpoint,
        ]);

        // Increment counters
        $this->increment('request_total');
        $this->increment("request_status_{$statusCode}");

        // Track slow requests
        if ($duration > 1.0) {
            $this->increment('request_slow');
        }
    }

    /**
     * Record error
     */
    public function recordError(string $type, string $message, array $context = []): void
    {
        $this->record('error', [
            'type' => $type,
            'message' => $message,
            'context' => $context,
        ]);

        $this->increment('error_total');
        $this->increment("error_type_{$type}");
    }

    /**
     * Get summary statistics
     */
    public function getSummary(int $minutes = 60): array
    {
        $fromTimestamp = time() - ($minutes * 60);
        $toTimestamp = time();

        $requests = $this->getMetrics('request', $fromTimestamp, $toTimestamp);
        $errors = $this->getMetrics('error', $fromTimestamp, $toTimestamp);

        $totalRequests = count($requests);
        $totalErrors = count($errors);

        $durations = array_column($requests, 'data.duration');
        $avgDuration = $durations ? array_sum($durations) / count($durations) : 0;
        $maxDuration = $durations ? max($durations) : 0;

        $statusCodes = [];
        foreach ($requests as $request) {
            $code = $request['data']['status_code'] ?? 0;
            $statusCodes[$code] = ($statusCodes[$code] ?? 0) + 1;
        }

        return [
            'period_minutes' => $minutes,
            'total_requests' => $totalRequests,
            'total_errors' => $totalErrors,
            'error_rate' => $totalRequests > 0 ? ($totalErrors / $totalRequests) * 100 : 0,
            'avg_duration' => round($avgDuration, 3),
            'max_duration' => round($maxDuration, 3),
            'status_codes' => $statusCodes,
        ];
    }

    /**
     * Clear old metrics
     */
    public function cleanup(): int
    {
        $redis = Yii::$app->redis;
        $cutoffDate = date('Y-m-d', strtotime("-{$this->retentionDays} days"));

        $pattern = $this->redisPrefix . '*:' . $cutoffDate;
        $keys = $redis->keys($pattern);

        $deleted = 0;
        foreach ($keys as $key) {
            $redis->del($key);
            $deleted++;
        }

        return $deleted;
    }
}
