<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\web\ServiceUnavailableHttpException;

/**
 * Circuit Breaker Pattern Implementation
 */
class CircuitBreaker extends Component
{
    public const STATE_CLOSED = 'closed';
    public const STATE_OPEN = 'open';
    public const STATE_HALF_OPEN = 'half_open';

    public int $failureThreshold = 5;      // Number of failures before opening
    public int $successThreshold = 2;      // Number of successes to close from half-open
    public int $openDuration = 60;         // Seconds to keep circuit open

    private string $redisPrefix = 'circuit_breaker:';

    /**
     * Execute a function with circuit breaker protection
     *
     * @throws ServiceUnavailableHttpException
     */
    public function call(string $serviceName, callable $callback)
    {
        $state = $this->getState($serviceName);

        // If circuit is open, check if we can try half-open
        if ($state['state'] === self::STATE_OPEN) {
            if (time() - $state['opened_at'] >= $this->openDuration) {
                $this->setState($serviceName, self::STATE_HALF_OPEN);
            } else {
                throw new ServiceUnavailableHttpException(
                    "Service {$serviceName} is temporarily unavailable (circuit open)",
                    503
                );
            }
        }

        try {
            $result = $callback();

            // Success
            $this->recordSuccess($serviceName);

            return $result;
        } catch (\Exception $e) {
            // Failure
            $this->recordFailure($serviceName);

            throw $e;
        }
    }

    /**
     * Record a successful call
     */
    public function recordSuccess(string $serviceName): void
    {
        $redis = Yii::$app->redis;
        $key = $this->redisPrefix . $serviceName;

        $state = $this->getState($serviceName);

        if ($state['state'] === self::STATE_HALF_OPEN) {
            $successCount = $state['success_count'] + 1;

            if ($successCount >= $this->successThreshold) {
                // Close the circuit
                $this->setState($serviceName, self::STATE_CLOSED);
                Yii::info("Circuit breaker for {$serviceName} closed", __METHOD__);
            } else {
                $redis->hset($key, 'success_count', $successCount);
            }
        } elseif ($state['state'] === self::STATE_CLOSED) {
            // Reset failure count on success
            $redis->hset($key, 'failure_count', 0);
        }
    }

    /**
     * Record a failed call
     */
    public function recordFailure(string $serviceName): void
    {
        $redis = Yii::$app->redis;
        $key = $this->redisPrefix . $serviceName;

        $state = $this->getState($serviceName);

        if ($state['state'] === self::STATE_HALF_OPEN) {
            // Any failure in half-open state opens the circuit again
            $this->setState($serviceName, self::STATE_OPEN);
            Yii::warning("Circuit breaker for {$serviceName} reopened", __METHOD__);
        } elseif ($state['state'] === self::STATE_CLOSED) {
            $failureCount = $state['failure_count'] + 1;

            if ($failureCount >= $this->failureThreshold) {
                $this->setState($serviceName, self::STATE_OPEN);
                Yii::warning("Circuit breaker for {$serviceName} opened after {$failureCount} failures", __METHOD__);
            } else {
                $redis->hset($key, 'failure_count', $failureCount);
            }
        }
    }

    /**
     * Get current state of circuit breaker
     */
    public function getState(string $serviceName): array
    {
        $redis = Yii::$app->redis;
        $key = $this->redisPrefix . $serviceName;

        $state = $redis->hgetall($key);

        if (empty($state)) {
            return [
                'state' => self::STATE_CLOSED,
                'failure_count' => 0,
                'success_count' => 0,
                'opened_at' => 0,
            ];
        }

        return [
            'state' => $state['state'] ?? self::STATE_CLOSED,
            'failure_count' => (int)($state['failure_count'] ?? 0),
            'success_count' => (int)($state['success_count'] ?? 0),
            'opened_at' => (int)($state['opened_at'] ?? 0),
        ];
    }

    /**
     * Set circuit breaker state
     */
    private function setState(string $serviceName, string $state): void
    {
        $redis = Yii::$app->redis;
        $key = $this->redisPrefix . $serviceName;

        $data = [
            'state' => $state,
            'failure_count' => 0,
            'success_count' => 0,
        ];

        if ($state === self::STATE_OPEN) {
            $data['opened_at'] = time();
        }

        $redis->hmset($key, ...$this->flattenArray($data));
        $redis->expire($key, 3600); // 1 hour TTL
    }

    /**
     * Reset circuit breaker
     */
    public function reset(string $serviceName): void
    {
        $redis = Yii::$app->redis;
        $key = $this->redisPrefix . $serviceName;
        $redis->del($key);
    }

    /**
     * Flatten array for Redis HMSET
     */
    private function flattenArray(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $result[] = $key;
            $result[] = $value;
        }
        return $result;
    }

    /**
     * Check if circuit is open
     */
    public function isOpen(string $serviceName): bool
    {
        $state = $this->getState($serviceName);
        return $state['state'] === self::STATE_OPEN;
    }
}
