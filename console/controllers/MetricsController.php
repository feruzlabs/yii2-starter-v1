<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;

/**
 * Metrics Controller
 * Collects and exports application metrics
 */
class MetricsController extends Controller
{
    /**
     * Collect metrics continuously
     */
    public function actionCollect()
    {
        echo "Starting Metrics Collector...\n";

        while (true) {
            try {
                $this->collectMetrics();
                sleep(15); // Collect every 15 seconds
            } catch (\Exception $e) {
                Yii::error("Metrics collection error: " . $e->getMessage(), __METHOD__);
                sleep(30);
            }
        }
    }

    /**
     * Collect metrics
     */
    private function collectMetrics(): void
    {
        $metricsCollector = Yii::$app->metricsCollector;

        // Get summary
        $summary = $metricsCollector->getSummary(15);

        echo sprintf(
            "Metrics [%s] - Requests: %d, Errors: %d, Error Rate: %.2f%%, Avg Duration: %.3fs\n",
            date('H:i:s'),
            $summary['total_requests'],
            $summary['total_errors'],
            $summary['error_rate'],
            $summary['avg_duration']
        );

        // Check for high error rate
        if ($summary['error_rate'] > 5) {
            echo "⚠️  High error rate detected!\n";

            if (Yii::$app->has('alertManager')) {
                Yii::$app->alertManager->sendAlert(
                    'high_error_rate',
                    "Error rate is {$summary['error_rate']}%",
                    $summary
                );
            }
        }
    }

    /**
     * Export metrics (for Prometheus or other systems)
     */
    public function actionExport()
    {
        $metricsCollector = Yii::$app->metricsCollector;
        $summary = $metricsCollector->getSummary(60);

        echo "# HELP api_requests_total Total number of API requests\n";
        echo "# TYPE api_requests_total counter\n";
        echo "api_requests_total {$summary['total_requests']}\n\n";

        echo "# HELP api_errors_total Total number of API errors\n";
        echo "# TYPE api_errors_total counter\n";
        echo "api_errors_total {$summary['total_errors']}\n\n";

        echo "# HELP api_request_duration_avg Average request duration in seconds\n";
        echo "# TYPE api_request_duration_avg gauge\n";
        echo "api_request_duration_avg {$summary['avg_duration']}\n\n";

        echo "# HELP api_request_duration_max Maximum request duration in seconds\n";
        echo "# TYPE api_request_duration_max gauge\n";
        echo "api_request_duration_max {$summary['max_duration']}\n\n";
    }

    /**
     * Cleanup old metrics
     */
    public function actionCleanup()
    {
        echo "Cleaning up old metrics...\n";

        $metricsCollector = Yii::$app->metricsCollector;
        $deleted = $metricsCollector->cleanup();

        echo "Deleted {$deleted} old metric entries\n";
    }
}
