<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use Symfony\Component\Yaml\Yaml;

/**
 * Alert Manager
 * Manages system alerts and notifications
 */
class AlertManager extends Component
{
    public string $configFile = '@common/config/alerts.yml';
    public array $channels = [];

    private array $alertRules = [];

    /**
     * Initialize component
     */
    public function init(): void
    {
        parent::init();
        $this->loadAlertRules();
    }

    /**
     * Load alert rules from YAML config
     */
    private function loadAlertRules(): void
    {
        $configPath = Yii::getAlias($this->configFile);

        if (file_exists($configPath)) {
            $this->alertRules = Yaml::parseFile($configPath);
        }
    }

    /**
     * Send alert
     */
    public function sendAlert(string $type, string $message, array $context = []): void
    {
        $rule = $this->getAlertRule($type);

        if (!$rule) {
            Yii::warning("No alert rule found for type: {$type}", __METHOD__);
            return;
        }

        // Check if alert should be sent (throttling)
        if (!$this->shouldSendAlert($type, $rule)) {
            return;
        }

        // Get channels for this alert type
        $channels = $rule['channels'] ?? ['log'];

        foreach ($channels as $channel) {
            $this->sendToChannel($channel, $type, $message, $context, $rule);
        }

        // Record alert history
        $this->recordAlert($type, $message, $context);
    }

    /**
     * Get alert rule by type
     */
    private function getAlertRule(string $type): ?array
    {
        return ArrayHelper::getValue($this->alertRules, "alerts.{$type}");
    }

    /**
     * Check if alert should be sent (throttling)
     */
    private function shouldSendAlert(string $type, array $rule): bool
    {
        $throttle = $rule['throttle'] ?? 300; // 5 minutes default

        $redis = Yii::$app->redis;
        $key = "alert:throttle:{$type}";

        $lastSent = $redis->get($key);

        if ($lastSent && (time() - $lastSent) < $throttle) {
            return false;
        }

        $redis->setex($key, $throttle, time());
        return true;
    }

    /**
     * Send alert to specific channel
     */
    private function sendToChannel(string $channel, string $type, string $message, array $context, array $rule): void
    {
        $severity = $rule['severity'] ?? 'warning';

        switch ($channel) {
            case 'log':
                $this->sendToLog($type, $message, $severity);
                break;

            case 'telegram':
                $this->sendToTelegram($type, $message, $context);
                break;

            case 'email':
                $this->sendToEmail($type, $message, $context);
                break;

            case 'slack':
                $this->sendToSlack($type, $message, $context);
                break;

            default:
                Yii::warning("Unknown alert channel: {$channel}", __METHOD__);
        }
    }

    /**
     * Send to log
     */
    private function sendToLog(string $type, string $message, string $severity): void
    {
        $logMessage = "[{$type}] {$message}";

        switch ($severity) {
            case 'critical':
            case 'error':
                Yii::error($logMessage, __METHOD__);
                break;
            case 'warning':
                Yii::warning($logMessage, __METHOD__);
                break;
            default:
                Yii::info($logMessage, __METHOD__);
        }
    }

    /**
     * Send to Telegram
     */
    private function sendToTelegram(string $type, string $message, array $context): void
    {
        $token = getenv('ALERT_TELEGRAM_TOKEN');
        $chatId = getenv('ALERT_TELEGRAM_CHAT_ID');

        if (!$token || !$chatId) {
            return;
        }

        $text = "🚨 *Alert: {$type}*\n\n{$message}";

        if (!empty($context)) {
            $text .= "\n\n*Details:*\n```\n" . json_encode($context, JSON_PRETTY_PRINT) . "\n```";
        }

        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);

        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Send to Email
     */
    private function sendToEmail(string $type, string $message, array $context): void
    {
        $emails = explode(',', getenv('ALERT_EMAILS') ?: '');
        $emails = array_filter(array_map('trim', $emails));

        if (empty($emails)) {
            return;
        }

        foreach ($emails as $email) {
            Yii::$app->mailer->compose()
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                ->setTo($email)
                ->setSubject("Alert: {$type}")
                ->setTextBody($message . "\n\nContext:\n" . print_r($context, true))
                ->send();
        }
    }

    /**
     * Send to Slack
     */
    private function sendToSlack(string $type, string $message, array $context): void
    {
        $webhookUrl = getenv('ALERT_SLACK_WEBHOOK');

        if (!$webhookUrl) {
            return;
        }

        $payload = [
            'text' => "🚨 Alert: {$type}",
            'attachments' => [
                [
                    'color' => 'danger',
                    'text' => $message,
                    'fields' => [
                        [
                            'title' => 'Context',
                            'value' => json_encode($context, JSON_PRETTY_PRINT),
                        ],
                    ],
                ],
            ],
        ];

        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Record alert in history
     */
    private function recordAlert(string $type, string $message, array $context): void
    {
        $redis = Yii::$app->redis;
        $key = "alert:history:" . date('Y-m-d');

        $alert = [
            'type' => $type,
            'message' => $message,
            'context' => $context,
            'timestamp' => time(),
        ];

        $redis->zadd($key, time(), json_encode($alert));
        $redis->expire($key, 30 * 86400); // 30 days
    }

    /**
     * Get alert history
     */
    public function getHistory(int $hours = 24): array
    {
        $redis = Yii::$app->redis;
        $fromTimestamp = time() - ($hours * 3600);
        $alerts = [];

        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $key = "alert:history:{$date}";

            $dayAlerts = $redis->zrangebyscore($key, $fromTimestamp, time());

            foreach ($dayAlerts as $alert) {
                $alerts[] = json_decode($alert, true);
            }
        }

        return $alerts;
    }
}
