<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'About';
?>

<h1><?= Html::encode($this->title) ?></h1>

<div style="line-height: 1.8;">
    <h2 style="color: #667eea; margin-top: 30px;">🚀 Yii2 Advanced Template with Docker</h2>

    <p style="font-size: 1.1em; margin: 20px 0;">
        This is a complete, production-ready Yii2 Advanced Template starter kit with Docker support,
        including API protection, monitoring, and microservices architecture.
    </p>

    <h3 style="color: #667eea; margin-top: 30px;">✨ Features</h3>
    <ul style="margin-left: 20px; margin-top: 15px;">
        <li>🐳 Docker-based development environment</li>
        <li>🔥 PHP 8.3 with PHP-FPM</li>
        <li>🌐 Nginx web server with optimized configuration</li>
        <li>🗄️ PostgreSQL 15 database</li>
        <li>⚡ Redis 7 for caching and sessions</li>
        <li>📮 RabbitMQ 3.12 message broker</li>
        <li>🛡️ API Protection (Rate Limiting, Throttling, Circuit Breaker)</li>
        <li>📊 Monitoring and Metrics Collection</li>
        <li>🚨 Alert System (Telegram, Email, Slack)</li>
    </ul>

    <h3 style="color: #667eea; margin-top: 30px;">🏗️ Architecture</h3>
    <ul style="margin-left: 20px; margin-top: 15px;">
        <li><strong>Frontend:</strong> Public-facing website (Port 8080)</li>
        <li><strong>Backend:</strong> Admin panel (Port 8081)</li>
        <li><strong>API:</strong> REST API with protection (Port 8082)</li>
        <li><strong>Common:</strong> Shared components and models</li>
        <li><strong>Console:</strong> Background workers and commands</li>
    </ul>

    <h3 style="color: #667eea; margin-top: 30px;">📚 Documentation</h3>
    <p style="margin-top: 15px;">
        For more information, check out:
    </p>
    <ul style="margin-left: 20px; margin-top: 10px;">
        <li><a href="https://www.yiiframework.com/doc/guide/2.0/en" target="_blank" style="color: #667eea;">Yii 2.0 Guide</a></li>
        <li><a href="https://docs.docker.com/" target="_blank" style="color: #667eea;">Docker Documentation</a></li>
        <li><a href="https://www.rabbitmq.com/getstarted.html" target="_blank" style="color: #667eea;">RabbitMQ Tutorials</a></li>
    </ul>
</div>
