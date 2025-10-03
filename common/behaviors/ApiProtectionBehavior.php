<?php

namespace common\behaviors;

use Yii;
use yii\base\ActionEvent;
use yii\base\Behavior;
use yii\web\Controller;

/**
 * API Protection Behavior
 * Applies rate limiting, throttling, and circuit breaker protection
 */
class ApiProtectionBehavior extends Behavior
{
    public bool $enableRateLimit = true;
    public bool $enableThrottle = true;
    public bool $enableCircuitBreaker = false;

    public string $priority = 'normal';
    public string $serviceName = 'api';

    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
            Controller::EVENT_AFTER_ACTION => 'afterAction',
        ];
    }

    /**
     * Before action handler
     */
    public function beforeAction(ActionEvent $event)
    {
        $startTime = microtime(true);
        Yii::$app->params['requestStartTime'] = $startTime;

        try {
            // Apply throttling
            if ($this->enableThrottle && Yii::$app->has('throttler')) {
                Yii::$app->throttler->checkThrottle();
            }

            // Apply rate limiting
            if ($this->enableRateLimit && Yii::$app->has('rateLimiter')) {
                $this->applyRateLimit();
            }

            // Check circuit breaker
            if ($this->enableCircuitBreaker && Yii::$app->has('circuitBreaker')) {
                $this->checkCircuitBreaker();
            }

        } catch (\Exception $e) {
            // Record error
            if (Yii::$app->has('metricsCollector')) {
                Yii::$app->metricsCollector->recordError(
                    get_class($e),
                    $e->getMessage(),
                    ['code' => $e->getCode()]
                );
            }

            throw $e;
        }

        return $event->isValid;
    }

    /**
     * After action handler
     */
    public function afterAction($action, $result)
    {
        $startTime = Yii::$app->params['requestStartTime'] ?? microtime(true);
        $duration = microtime(true) - $startTime;

        // Record request metrics
        if (Yii::$app->has('metricsCollector')) {
            Yii::$app->metricsCollector->recordRequest(
                $duration,
                Yii::$app->response->statusCode,
                Yii::$app->request->url
            );
        }

        // Record circuit breaker success if enabled
        if ($this->enableCircuitBreaker && Yii::$app->has('circuitBreaker')) {
            Yii::$app->circuitBreaker->recordSuccess($this->serviceName);
        }

        return $result;
    }

    /**
     * Apply rate limiting
     */
    private function applyRateLimit(): void
    {
        $rateLimiter = Yii::$app->rateLimiter;

        // Determine rate limit key (user ID or IP)
        $user = Yii::$app->user;
        $key = !$user->isGuest ? 'user:' . $user->id : 'ip:' . Yii::$app->request->userIP;

        // Determine priority
        $priority = $this->priority;
        if ($user && !$user->isGuest) {
            $priority = $rateLimiter->getUserPriority($user->identity);
        }

        // Check rate limit
        $rateLimiter->checkLimit($key, $priority);
    }

    /**
     * Check circuit breaker
     */
    private function checkCircuitBreaker(): void
    {
        $circuitBreaker = Yii::$app->circuitBreaker;

        if ($circuitBreaker->isOpen($this->serviceName)) {
            throw new \yii\web\ServiceUnavailableHttpException(
                "Service {$this->serviceName} is temporarily unavailable"
            );
        }
    }
}
