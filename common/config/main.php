<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => \yii\redis\Cache::class,
            'redis' => [
                'hostname' => getenv('REDIS_HOST') ?: 'redis',
                'port' => getenv('REDIS_PORT') ?: 6379,
                'database' => 0,
            ],
        ],
        'db' => [
            'class' => \yii\db\Connection::class,
            'dsn' => getenv('DB_DSN') ?: 'pgsql:host=pgsql;dbname=yii2advanced',
            'username' => getenv('DB_USERNAME') ?: 'yii2',
            'password' => getenv('DB_PASSWORD') ?: 'secret',
            'charset' => 'utf8',
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 3600,
            'schemaCache' => 'cache',
        ],
        'redis' => [
            'class' => \yii\redis\Connection::class,
            'hostname' => getenv('REDIS_HOST') ?: 'redis',
            'port' => getenv('REDIS_PORT') ?: 6379,
            'database' => 0,
        ],
        'rabbitmq' => [
            'class' => 'common\components\RabbitMQComponent',
            'host' => getenv('RABBITMQ_HOST') ?: 'rabbitmq',
            'port' => getenv('RABBITMQ_PORT') ?: 5672,
            'user' => getenv('RABBITMQ_USER') ?: 'guest',
            'password' => getenv('RABBITMQ_PASSWORD') ?: 'guest',
            'vhost' => getenv('RABBITMQ_VHOST') ?: '/',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@common/mail',
        ],
        'rateLimiter' => [
            'class' => 'common\components\PriorityRateLimiter',
        ],
        'throttler' => [
            'class' => 'common\components\AdaptiveThrottler',
            'cpuThreshold' => getenv('THROTTLE_CPU_THRESHOLD') ?: 70,
            'memoryThreshold' => getenv('THROTTLE_MEMORY_THRESHOLD') ?: 80,
        ],
        'circuitBreaker' => [
            'class' => 'common\components\CircuitBreaker',
            'failureThreshold' => getenv('CIRCUIT_BREAKER_FAILURE_THRESHOLD') ?: 5,
            'openDuration' => getenv('CIRCUIT_BREAKER_OPEN_DURATION') ?: 60,
        ],
        'metricsCollector' => [
            'class' => 'common\components\MetricsCollector',
        ],
        'alertManager' => [
            'class' => 'common\components\AlertManager',
        ],
        'fpmMonitor' => [
            'class' => 'common\components\PhpFpmMonitor',
            'statusUrl' => getenv('FPM_STATUS_URL') ?: 'http://php-fpm:9001/fpm-status',
        ],
    ],
];
