<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\helpers\Html;

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; line-height: 1.6; color: #333; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        header h1 { font-size: 2.5em; margin-bottom: 10px; }
        header p { font-size: 1.1em; opacity: 0.9; }
        nav { background: white; padding: 15px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        nav ul { list-style: none; display: flex; gap: 30px; }
        nav a { text-decoration: none; color: #667eea; font-weight: 500; transition: color 0.3s; }
        nav a:hover { color: #764ba2; }
        main { background: white; margin: 30px 0; padding: 40px; border-radius: 10px; box-shadow: 0 2px 20px rgba(0,0,0,0.05); }
        footer { text-align: center; padding: 30px 0; color: #666; border-top: 1px solid #e0e0e0; margin-top: 50px; }
        .badge { display: inline-block; padding: 5px 15px; background: #4CAF50; color: white; border-radius: 20px; font-size: 0.9em; margin-left: 10px; }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<header>
    <div class="container">
        <h1>Yii2 Advanced Template <span class="badge">Frontend</span></h1>
        <p>Modern web application with Docker & API protection</p>
    </div>
</header>

<nav>
    <div class="container">
        <ul>
            <li><?= Html::a('Home', ['/site/index']) ?></li>
            <li><?= Html::a('About', ['/site/about']) ?></li>
            <li><a href="http://localhost:8082/health" target="_blank">API Health</a></li>
        </ul>
    </div>
</nav>

<main>
    <div class="container">
        <?= $content ?>
    </div>
</main>

<footer>
    <div class="container">
        <p>&copy; <?= date('Y') ?> Yii2 Advanced Template. Powered by Docker & PHP 8.3</p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
