<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\web\TooManyRequestsHttpException;

/**
 * Priority-based Rate Limiter using Token Bucket algorithm
 */
class PriorityRateLimiter extends Component
{
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_LOW = 'low';

    // Capacity and refill rates for different priorities
    public array $capacities = [
        self::PRIORITY_HIGH => 200,
        self::PRIORITY_NORMAL => 100,
        self::PRIORITY_LOW => 50,
    ];

    public array $refillRates = [
        self::PRIORITY_HIGH => 20,  // tokens per second
        self::PRIORITY_NORMAL => 10,
        self::PRIORITY_LOW => 5,
    ];

    private string $redisPrefix = 'rate_limit:';

    /**
     * Check if request is allowed
     *
     * @throws TooManyRequestsHttpException
     */
    public function checkLimit(string $key, string $priority = self::PRIORITY_NORMAL, int $cost = 1): bool
    {
        $capacity = $this->capacities[$priority] ?? $this->capacities[self::PRIORITY_NORMAL];
        $refillRate = $this->refillRates[$priority] ?? $this->refillRates[self::PRIORITY_NORMAL];

        $redisKey = $this->redisPrefix . $priority . ':' . $key;
        $redis = Yii::$app->redis;

        // Get current bucket state
        $bucket = $redis->hgetall($redisKey);

        $now = microtime(true);
        $tokens = $bucket['tokens'] ?? $capacity;
        $lastRefill = $bucket['last_refill'] ?? $now;

        // Calculate tokens to add based on time elapsed
        $elapsed = $now - $lastRefill;
        $tokensToAdd = $elapsed * $refillRate;
        $tokens = min($capacity, $tokens + $tokensToAdd);

        // Check if enough tokens
        if ($tokens >= $cost) {
            $tokens -= $cost;

            // Update bucket
            $redis->hmset($redisKey, 'tokens', $tokens, 'last_refill', $now);
            $redis->expire($redisKey, 3600); // 1 hour TTL

            return true;
        }

        // Not enough tokens
        $retryAfter = ceil(($cost - $tokens) / $refillRate);

        throw new TooManyRequestsHttpException(
            "Rate limit exceeded. Retry after {$retryAfter} seconds.",
            429
        );
    }

    /**
     * Get current token count
     */
    public function getTokens(string $key, string $priority = self::PRIORITY_NORMAL): float
    {
        $capacity = $this->capacities[$priority] ?? $this->capacities[self::PRIORITY_NORMAL];
        $refillRate = $this->refillRates[$priority] ?? $this->refillRates[self::PRIORITY_NORMAL];

        $redisKey = $this->redisPrefix . $priority . ':' . $key;
        $redis = Yii::$app->redis;

        $bucket = $redis->hgetall($redisKey);

        if (empty($bucket)) {
            return $capacity;
        }

        $now = microtime(true);
        $tokens = $bucket['tokens'] ?? $capacity;
        $lastRefill = $bucket['last_refill'] ?? $now;

        $elapsed = $now - $lastRefill;
        $tokensToAdd = $elapsed * $refillRate;

        return min($capacity, $tokens + $tokensToAdd);
    }

    /**
     * Reset rate limit for a key
     */
    public function reset(string $key, string $priority = self::PRIORITY_NORMAL): void
    {
        $redisKey = $this->redisPrefix . $priority . ':' . $key;
        Yii::$app->redis->del($redisKey);
    }

    /**
     * Get user priority based on role or other criteria
     */
    public function getUserPriority($user): string
    {
        if ($user && method_exists($user, 'isPremium') && $user->isPremium()) {
            return self::PRIORITY_HIGH;
        }

        if ($user && !$user->isGuest) {
            return self::PRIORITY_NORMAL;
        }

        return self::PRIORITY_LOW;
    }
}
