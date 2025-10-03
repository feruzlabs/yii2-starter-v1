<?php

return [
    'components' => [
        'db' => [
            'class' => \yii\db\Connection::class,
            'dsn' => getenv('DB_DSN') ?: 'pgsql:host=pgsql;dbname=yii2advanced',
            'username' => getenv('DB_USERNAME') ?: 'yii2',
            'password' => getenv('DB_PASSWORD') ?: 'secret',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@common/mail',
            'useFileTransport' => true,
        ],
    ],
];
