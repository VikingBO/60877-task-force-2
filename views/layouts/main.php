<?php

/** @var yii\web\View $this */

/** @var string $content */

use app\assets\MainAsset;
use app\widgets\Alert;
use yii\bootstrap4\Breadcrumbs;
use yii\bootstrap4\Html;
use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\helpers\Url;

MainAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>

    <script src="https://api-maps.yandex.ru/2.1/?apikey=e666f398-c983-4bde-8f14-e3fec900592a&lang=ru_RU" type="text/javascript"></script>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>
<?php if (\Yii::$app->controller->id !== "register"): ?>
    <header class="page-header">
        <nav class="main-nav">


            <a href='<?php echo Url::to(['/']); ?>' class="header-logo">
                <?php echo Html::img(Yii::$app->urlManager->createUrl('img/logotype.png')); ?>
            </a>

            <div class="nav-wrapper">
                <ul class="nav-list">
                    <li class="list-item list-item--active">
                        <a href="<?php echo Url::to(['/tasks']); ?>" class="link link--nav">Новое</a>
                    </li>
                    <li class="list-item">
                        <a href="<?php echo Url::to(['/my-tasks?status=new']); ?>" class="link link--nav">Мои задания</a>
                    </li>
                    <li class="list-item">
                        <a href="<?php echo Url::to(['tasks/add']); ?>" class="link link--nav">Создать задание</a>
                    </li>
                    <li class="list-item">
                        <a href="<?php echo Url::to(['/edit-profile']); ?>" class="link link--nav">Настройки</a>
                    </li>
                </ul>
            </div>
        </nav>
        <div class="user-block">
            <?php
            $isGuest = Yii::$app->user->isGuest;
            if (!$isGuest):?>
                <a href="#">
                    <?php echo Html::img('../img/' . \Yii::$app->user->identity->user_img); ?>
                </a>

                <div class="user-menu">

                    <p class="user-name"><?php echo Yii::$app->user->identity->name; ?></p>
                    <div class="popup-head">
                        <ul class="popup-menu">
                            <li class="menu-item">
                                <a href="#" class="link">Настройки</a>
                            </li>
                            <li class="menu-item">
                                <a href="#" class="link">Связаться с нами</a>
                            </li>
                            <li class="menu-item">
                                <a href="<?php echo Url::to(['user/logout']); ?>" class="link">Выход из системы</a>
                            </li>
                        </ul>
                    </div>

                </div>

            <?php endif; ?>
        </div>

    </header>
<?php endif; ?>
<main role="main" class="flex-shrink-0">
    <div class="container">

        <?= $content ?>
    </div>
</main>

<footer class="footer mt-auto py-3 text-muted">
    <div class="container">
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
