<?php

/** @var yii\web\View $this */

$this->title = 'Welcome to Yii2 Advanced Template';
?>

<div style="text-align: center; padding: 50px 0;">
    <h1 style="font-size: 3em; color: #667eea; margin-bottom: 20px;">🚀 Welcome to Yii2 Frontend!</h1>
    <p style="font-size: 1.3em; color: #666; margin-bottom: 40px;">
        Your modern web application is ready to go!
    </p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 40px;">

    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);">
        <h3 style="font-size: 1.5em; margin-bottom: 15px;">✅ Frontend</h3>
        <p style="margin-bottom: 10px;">Running on port <strong>8080</strong></p>
        <p style="opacity: 0.9;">This is the public-facing website</p>
    </div>

    <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(245, 87, 108, 0.3);">
        <h3 style="font-size: 1.5em; margin-bottom: 15px;">🔧 Backend</h3>
        <p style="margin-bottom: 10px;">Running on port <strong>8081</strong></p>
        <p style="opacity: 0.9;"><a href="http://localhost:8081" style="color: white; text-decoration: underline;">Admin Panel →</a></p>
    </div>

    <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);">
        <h3 style="font-size: 1.5em; margin-bottom: 15px;">⚡ API</h3>
        <p style="margin-bottom: 10px;">Running on port <strong>8082</strong></p>
        <p style="opacity: 0.9;"><a href="http://localhost:8082/health" style="color: white; text-decoration: underline;" target="_blank">Health Check →</a></p>
    </div>

</div>

<div style="margin-top: 50px; padding: 30px; background: #f8f9fa; border-radius: 10px; border-left: 5px solid #667eea;">
    <h2 style="color: #667eea; margin-bottom: 20px;">📊 System Status</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <div>
            <strong>PHP Version:</strong>
            <p><?= PHP_VERSION ?></p>
        </div>
        <div>
            <strong>Yii Version:</strong>
            <p><?= Yii::getVersion() ?></p>
        </div>
        <div>
            <strong>Environment:</strong>
            <p><?= YII_ENV ?></p>
        </div>
        <div>
            <strong>Debug Mode:</strong>
            <p><?= YII_DEBUG ? 'Enabled' : 'Disabled' ?></p>
        </div>
    </div>
</div>

<div style="margin-top: 40px; padding: 30px; background: #fff3cd; border-radius: 10px; border-left: 5px solid #ffc107;">
    <h3 style="color: #856404; margin-bottom: 15px;">🔗 Quick Links</h3>
    <ul style="list-style: none; padding: 0;">
        <li style="margin-bottom: 10px;">
            <strong>API Health:</strong>
            <a href="http://localhost:8082/health" target="_blank" style="color: #667eea;">http://localhost:8082/health</a>
        </li>
        <li style="margin-bottom: 10px;">
            <strong>RabbitMQ:</strong>
            <a href="http://localhost:15672" target="_blank" style="color: #667eea;">http://localhost:15672</a>
            (guest/guest)
        </li>
        <li style="margin-bottom: 10px;">
            <strong>Database:</strong> PostgreSQL on port 5432
        </li>
        <li style="margin-bottom: 10px;">
            <strong>Redis:</strong> localhost:6379
        </li>
    </ul>
</div>
