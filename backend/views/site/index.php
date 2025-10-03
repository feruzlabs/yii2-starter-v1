<?php

/** @var yii\web\View $this */

$this->title = 'Admin Dashboard';
?>

<div style="text-align: center; padding: 50px 0;">
    <h1 style="font-size: 3em; color: #f5576c; margin-bottom: 20px;">🔧 Admin Dashboard</h1>
    <p style="font-size: 1.3em; color: #666; margin-bottom: 40px;">
        Manage your application from here
    </p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-top: 40px;">

    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3); text-align: center;">
        <div style="font-size: 3em; margin-bottom: 10px;">📊</div>
        <h3 style="font-size: 1.5em; margin-bottom: 10px;">Orders</h3>
        <p style="font-size: 2em; font-weight: bold;"><?php
        try {
            echo \common\models\Order::find()->count();
        } catch (\Exception $e) {
            echo '0';
        }
        ?></p>
        <p style="opacity: 0.9;">Total orders</p>
    </div>

    <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(245, 87, 108, 0.3); text-align: center;">
        <div style="font-size: 3em; margin-bottom: 10px;">👥</div>
        <h3 style="font-size: 1.5em; margin-bottom: 10px;">Users</h3>
        <p style="font-size: 2em; font-weight: bold;"><?php
        try {
            echo \common\models\User::find()->count();
        } catch (\Exception $e) {
            echo '0';
        }
        ?></p>
        <p style="opacity: 0.9;">Registered users</p>
    </div>

    <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3); text-align: center;">
        <div style="font-size: 3em; margin-bottom: 10px;">📮</div>
        <h3 style="font-size: 1.5em; margin-bottom: 10px;">Messages</h3>
        <p style="font-size: 2em; font-weight: bold;"><?php
        try {
            echo \common\models\OutboxMessage::find()->where(['status' => 'pending'])->count();
        } catch (\Exception $e) {
            echo '0';
        }
        ?></p>
        <p style="opacity: 0.9;">Pending messages</p>
    </div>

    <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(250, 112, 154, 0.3); text-align: center;">
        <div style="font-size: 3em; margin-bottom: 10px;">⚡</div>
        <h3 style="font-size: 1.5em; margin-bottom: 10px;">Performance</h3>
        <p style="font-size: 2em; font-weight: bold;">Good</p>
        <p style="opacity: 0.9;">System status</p>
    </div>

</div>

<div style="margin-top: 50px; padding: 30px; background: #f8f9fa; border-radius: 10px; border-left: 5px solid #f5576c;">
    <h2 style="color: #f5576c; margin-bottom: 20px;">🎛️ System Information</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
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
        <div>
            <strong>Database:</strong>
            <p><?php
            try {
                Yii::$app->db->createCommand('SELECT 1')->queryOne();
                echo '✅ Connected';
            } catch (\Exception $e) {
                echo '❌ Not Connected';
            }
            ?></p>
        </div>
        <div>
            <strong>Redis:</strong>
            <p><?php
            try {
                Yii::$app->redis->ping();
                echo '✅ Connected';
            } catch (\Exception $e) {
                echo '❌ Not Connected';
            }
            ?></p>
        </div>
        <div>
            <strong>RabbitMQ:</strong>
            <p><?php
            try {
                $connection = Yii::$app->rabbitmq->getConnection();
                echo $connection->isConnected() ? '✅ Connected' : '❌ Not Connected';
            } catch (\Exception $e) {
                echo '❌ Not Connected';
            }
            ?></p>
        </div>
        <div>
            <strong>Server Time:</strong>
            <p><?= date('Y-m-d H:i:s') ?></p>
        </div>
    </div>
</div>

<div style="margin-top: 40px; padding: 30px; background: #d1ecf1; border-radius: 10px; border-left: 5px solid #0c5460;">
    <h3 style="color: #0c5460; margin-bottom: 15px;">🔗 Quick Access</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <a href="http://localhost:8080" target="_blank" style="padding: 15px; background: white; border-radius: 5px; text-decoration: none; color: #0c5460; text-align: center; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <strong>Frontend →</strong>
        </a>
        <a href="http://localhost:8082/health" target="_blank" style="padding: 15px; background: white; border-radius: 5px; text-decoration: none; color: #0c5460; text-align: center; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <strong>API Health →</strong>
        </a>
        <a href="http://localhost:15672" target="_blank" style="padding: 15px; background: white; border-radius: 5px; text-decoration: none; color: #0c5460; text-align: center; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <strong>RabbitMQ →</strong>
        </a>
        <a href="<?= \yii\helpers\Url::to(['/gii']) ?>" style="padding: 15px; background: white; border-radius: 5px; text-decoration: none; color: #0c5460; text-align: center; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <strong>Gii Generator →</strong>
        </a>
    </div>
</div>
