<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;

/**
 * FPM Monitor Controller
 * Monitors PHP-FPM status and sends alerts
 */
class FpmMonitorController extends Controller
{
    /**
     * Run FPM monitor
     */
    public function actionRun()
    {
        echo "Starting FPM Monitor...\n";

        while (true) {
            try {
                $this->checkFpmStatus();
                sleep(10); // Check every 10 seconds
            } catch (\Exception $e) {
                Yii::error("FPM monitor error: " . $e->getMessage(), __METHOD__);
                sleep(30);
            }
        }
    }

    /**
     * Check FPM status
     */
    private function checkFpmStatus(): void
    {
        $fpmMonitor = Yii::$app->fpmMonitor;

        // Get metrics
        $metrics = $fpmMonitor->getMetrics();

        if (!$metrics['healthy']) {
            echo "⚠️  FPM is unhealthy!\n";
            echo "Warnings: " . implode(', ', $metrics['warnings']) . "\n";

            // Send alert
            $fpmMonitor->checkAndAlert();
        } else {
            echo "✓ FPM is healthy - Utilization: {$metrics['utilization_percent']}%\n";
        }

        // Record metrics
        $fpmMonitor->recordMetrics();

        // Display current status
        echo sprintf(
            "Active: %d, Total: %d, Idle: %d, Queue: %d\n",
            $metrics['active_processes'],
            $metrics['total_processes'],
            $metrics['idle_processes'],
            $metrics['listen_queue']
        );
    }
}
