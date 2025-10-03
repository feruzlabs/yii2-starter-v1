<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;

/**
 * Alert Controller
 * Checks alert rules and sends notifications
 */
class AlertController extends Controller
{
    /**
     * Run alert checker
     */
    public function actionCheck()
    {
        echo "Starting Alert Checker...\n";

        while (true) {
            try {
                $this->checkAlerts();
                sleep(30); // Check every 30 seconds
            } catch (\Exception $e) {
                Yii::error("Alert checker error: " . $e->getMessage(), __METHOD__);
                sleep(60);
            }
        }
    }

    /**
     * Check alert conditions
     */
    private function checkAlerts(): void
    {
        echo "[" . date('H:i:s') . "] Checking alert conditions...\n";

        // Check system load
        $this->checkSystemLoad();

        // Check FPM status
        $this->checkFpmStatus();

        // Check error rate
        $this->checkErrorRate();

        // Check circuit breakers
        $this->checkCircuitBreakers();
    }

    /**
     * Check system load
     */
    private function checkSystemLoad(): void
    {
        if (!Yii::$app->has('throttler')) {
            return;
        }

        $metrics = Yii::$app->throttler->getSystemMetrics();

        if ($metrics['cpu'] > 80) {
            echo "⚠️  High CPU usage: {$metrics['cpu']}%\n";

            if (Yii::$app->has('alertManager')) {
                Yii::$app->alertManager->sendAlert(
                    'high_cpu_usage',
                    "CPU usage is {$metrics['cpu']}%",
                    $metrics
                );
            }
        }

        if ($metrics['memory'] > 85) {
            echo "⚠️  High memory usage: {$metrics['memory']}%\n";

            if (Yii::$app->has('alertManager')) {
                Yii::$app->alertManager->sendAlert(
                    'high_memory_usage',
                    "Memory usage is {$metrics['memory']}%",
                    $metrics
                );
            }
        }
    }

    /**
     * Check FPM status
     */
    private function checkFpmStatus(): void
    {
        if (!Yii::$app->has('fpmMonitor')) {
            return;
        }

        $metrics = Yii::$app->fpmMonitor->getMetrics();

        if (!$metrics['healthy']) {
            echo "⚠️  FPM unhealthy: " . implode(', ', $metrics['warnings']) . "\n";
        }
    }

    /**
     * Check error rate
     */
    private function checkErrorRate(): void
    {
        if (!Yii::$app->has('metricsCollector')) {
            return;
        }

        $summary = Yii::$app->metricsCollector->getSummary(5);

        if ($summary['error_rate'] > 10) {
            echo "⚠️  High error rate: {$summary['error_rate']}%\n";
        }
    }

    /**
     * Check circuit breakers
     */
    private function checkCircuitBreakers(): void
    {
        // This would check known circuit breakers
        // For now, just a placeholder
    }

    /**
     * List alert history
     */
    public function actionHistory($hours = 24)
    {
        if (!Yii::$app->has('alertManager')) {
            echo "AlertManager not configured\n";
            return;
        }

        $history = Yii::$app->alertManager->getHistory($hours);

        echo "Alert History (last {$hours} hours):\n";
        echo str_repeat('=', 80) . "\n";

        foreach ($history as $alert) {
            $time = date('Y-m-d H:i:s', $alert['timestamp']);
            echo "[{$time}] {$alert['type']}: {$alert['message']}\n";
        }

        echo "\nTotal alerts: " . count($history) . "\n";
    }
}
