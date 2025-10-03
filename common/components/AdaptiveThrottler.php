<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\web\ServiceUnavailableHttpException;

/**
 * Adaptive Throttler
 * Automatically adjusts request processing based on system load
 */
class AdaptiveThrottler extends Component
{
    public float $cpuThreshold = 70.0;      // CPU threshold percentage
    public float $memoryThreshold = 80.0;   // Memory threshold percentage
    public int $checkInterval = 5;          // Check interval in seconds

    private string $redisKey = 'throttler:state';

    /**
     * Check if request should be throttled
     *
     * @throws ServiceUnavailableHttpException
     */
    public function checkThrottle(): bool
    {
        $metrics = $this->getSystemMetrics();

        // Check CPU usage
        if ($metrics['cpu'] > $this->cpuThreshold) {
            $this->logThrottle('CPU', $metrics['cpu']);
            throw new ServiceUnavailableHttpException(
                'Service temporarily overloaded. Please try again later.',
                503
            );
        }

        // Check memory usage
        if ($metrics['memory'] > $this->memoryThreshold) {
            $this->logThrottle('Memory', $metrics['memory']);
            throw new ServiceUnavailableHttpException(
                'Service temporarily overloaded. Please try again later.',
                503
            );
        }

        return true;
    }

    /**
     * Get system metrics
     */
    public function getSystemMetrics(): array
    {
        $redis = Yii::$app->redis;
        $cached = $redis->get($this->redisKey);

        if ($cached) {
            return json_decode($cached, true);
        }

        // Get CPU usage
        $cpu = $this->getCpuUsage();

        // Get memory usage
        $memory = $this->getMemoryUsage();

        $metrics = [
            'cpu' => $cpu,
            'memory' => $memory,
            'timestamp' => time(),
        ];

        // Cache for check interval
        $redis->setex($this->redisKey, $this->checkInterval, json_encode($metrics));

        return $metrics;
    }

    /**
     * Get CPU usage percentage
     */
    private function getCpuUsage(): float
    {
        if (stripos(PHP_OS, 'WIN') === 0) {
            // Windows
            $wmi = new \COM("Winmgmts://");
            $cpus = $wmi->ExecQuery("SELECT LoadPercentage FROM Win32_Processor");
            $cpuLoad = 0;
            $count = 0;
            foreach ($cpus as $cpu) {
                $cpuLoad += $cpu->LoadPercentage;
                $count++;
            }
            return $count > 0 ? $cpuLoad / $count : 0;
        } else {
            // Linux
            $load = sys_getloadavg();
            $cpuCount = $this->getCpuCount();
            return ($load[0] / $cpuCount) * 100;
        }
    }

    /**
     * Get memory usage percentage
     */
    private function getMemoryUsage(): float
    {
        if (stripos(PHP_OS, 'WIN') === 0) {
            // Windows
            $wmi = new \COM("Winmgmts://");
            $os = $wmi->ExecQuery("SELECT TotalVisibleMemorySize, FreePhysicalMemory FROM Win32_OperatingSystem");
            foreach ($os as $obj) {
                $total = $obj->TotalVisibleMemorySize;
                $free = $obj->FreePhysicalMemory;
                return (($total - $free) / $total) * 100;
            }
            return 0;
        } else {
            // Linux
            $meminfo = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+)/', $meminfo, $totalMatch);
            preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $availMatch);

            $total = $totalMatch[1] ?? 0;
            $available = $availMatch[1] ?? 0;

            if ($total > 0) {
                return (($total - $available) / $total) * 100;
            }
        }

        return 0;
    }

    /**
     * Get CPU count
     */
    private function getCpuCount(): int
    {
        if (stripos(PHP_OS, 'WIN') === 0) {
            return (int)getenv('NUMBER_OF_PROCESSORS') ?: 1;
        } else {
            return (int)shell_exec('nproc') ?: 1;
        }
    }

    /**
     * Log throttle event
     */
    private function logThrottle(string $type, float $value): void
    {
        Yii::warning("Throttling activated - {$type}: {$value}%", __METHOD__);

        // Store in metrics
        if (Yii::$app->has('metricsCollector')) {
            Yii::$app->metricsCollector->record('throttle', [
                'type' => $type,
                'value' => $value,
                'timestamp' => time(),
            ]);
        }
    }

    /**
     * Get current throttle state
     */
    public function getState(): array
    {
        return $this->getSystemMetrics();
    }
}
