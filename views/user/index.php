<?php

use app\models\TagsAttributes;

/**
 * @var app\models\Users $singleUser ;
 */


?>

<main class="main-content container">
    <div class="left-column">
        <h3 class="head-main"><?php echo $singleUser->name; ?></h3>
        <div class="user-card">
            <div class="photo-rate">
                <?php echo \yii\helpers\Html::img("@web/img/{$singleUser->user_img}", ['alt' => '', 'id' => '', 'width' => 191, 'height' => 190]); ?>
                <div class="card-rate">

                    <div class="stars-rating big">
                        <?php for ($i = 0; $i < $singleUser->getAvgRating(); $i++): ?>
                            <span class="fill-star"></span>
                        <?php endfor; ?>

                    </div>
                    <span class="current-rate">
                        <?php if (!is_null($singleUser->getAvgRating())): ?>
                            <?php echo $singleUser->getAvgRating(); ?>
                        <?php endif; ?>
                    </span>

                </div>
            </div>
            <p class="user-description">
                <?php echo $singleUser->quote; ?>
            </p>
        </div>
        <div class="specialization-bio">
            <div class="specialization">
                <p class="head-info">Специализации </p>
                <ul class="special-list">
                    <?php foreach ($singleUser->tags as $tag): ?>
                        <li class="special-item">
                            <a href="#" class="link link--regular"><?php echo $tag->attributes; ?></a>
                        </li>

                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="bio">
                <p class="head-info"> Био</p>
                <p class="bio-info">
                    <span class="country-info"><?php echo $singleUser->country; ?></span>,
                    <span class="town-info"><?php echo $singleUser->city; ?></span>,
                    <span class="age-info"><?php echo $singleUser->age; ?></span> лет
                </p>
            </div>
        </div>
        <h4 class="head-regular">Отзывы заказчиков</h4>

        <?php foreach ($singleUser->executorReplies as $reply): ?>

            <div class="response-card">
                <?php echo \yii\helpers\Html::img("@web/img/{$reply->userWriter->user_img}", ['alt' => '', 'id' => '', 'width' => 120, 'height' => 127]); ?>
                <div class="feedback-wrapper">
                    <p class="feedback">
                        <?php echo $reply->description; ?>
                    </p>
                    <p class="task">Задание «<a href="<?php echo $reply->getLastTask()->id; ?>"
                                                class="link link--small"><?php echo $reply->getLastTask()->name ?></a>»
                        выполнено
                    </p>
                </div>
                <div class="feedback-wrapper">

                    <div class="stars-rating small">
                        <?php for ($i = 0; $i < $reply->rate; $i++): ?>
                            <span class="fill-star">&nbsp;</span>
                        <?php endfor; ?>
                    </div>
                    <p class="info-text"><span class="current-time"><?php echo $reply->getWasOnSite(); ?> </span>назад
                    </p>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
    <div class="right-column">
        <div class="right-card black">
            <h4 class="head-card">Статистика исполнителя</h4>
            <dl class="black-list">
                <dt>Всего заказов</dt>
                <dd><?php echo count($singleUser->doneTasks); ?> выполнено, <?php echo count($singleUser->failedTasks); ?>  провалено</dd>
                <dt>Место в рейтинге</dt>
                <dd><?php echo $rating['rate'];?> место</dd>
                <dt>Дата регистрации</dt>
                <dd><?php echo date('Y-m-d H:m:s', $singleUser->dt_add); ?></dd>
                <dt>Статус</dt>
                <?php if ($singleUser->status === 1): ?>
                    <dd>Открыт для новых заказов</dd>
                <?php else: ?>
                    <dd>Закрыт для новых заказов</dd>
                <?php endif; ?>
            </dl>
        </div>
        <div class="right-card white">
            <h4 class="head-card">Контакты</h4>
            <ul class="enumeration-list">
                <li class="enumeration-item">
                    <a href="#" class="link link--block link--phone"><?php echo $singleUser->phone; ?></a>
                </li>
                <li class="enumeration-item">
                    <a href="#" class="link link--block link--email"><?php echo $singleUser->email; ?></a>
                </li>
                <li class="enumeration-item">
                    <a href="#" class="link link--block link--tg">@<?php echo $singleUser->telegram; ?></a>
                </li>
            </ul>
        </div>
    </div>
</main>