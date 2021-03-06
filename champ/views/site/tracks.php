<?php
/**
 * @var \yii\web\View        $this
 * @var array                $data
 * @var \yii\data\Pagination $pages
 */

use yii\widgets\LinkPager;

?>

    <div class="card-box">
        <h1><?= $this->context->pageTitle ?></h1>
        <p>
		    <?= \Yii::t('app', 'Обратите внимание - указан не класс соревнований, а класс спортсмена, показавшего лучшее время трассы. Чтобы узнать подробную информацию о результатах этапа, перейдите на его страницу.') ?>
        </p>
    </div>

    <div class="tracks-gallery card-box">
        <div class="row">
			<?php foreach ($data as $date => $info) { ?>
				<?php foreach ($info['items'] as $item) { ?>
                    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12 item">
                        <div class="img">
                            <figure class="effect-julia">
								<?= yii\helpers\Html::a(\yii\bootstrap\Html::img(\Yii::getAlias('@filesView') . '/' . $item['photoPath']),
									\Yii::getAlias('@filesView') . '/' . $item['photoPath'],
									['data-fancybox' => 'gallery']) ?>
                            </figure>
                            <figure class="effect-julia">
								<?= yii\helpers\Html::a(\yii\bootstrap\Html::img(\Yii::getAlias('@filesView') . '/' . $item['photoPath']),
									\Yii::getAlias('@filesView') . '/' . $item['photoPath']) ?>
                            </figure>
                        </div>
                        <div class="info">
							<?php if ($info['year']) { ?>
                                <div><?= \Yii::t('app', 'Год') ?>: <?= $info['year'] ?></div>
                                <div><?= \Yii::t('app', 'Лучшее время') ?>: <?= $item['bestTime'] ?></div>
                                <div><?= \Yii::t('app', 'Класс спортсмена') ?>: <?= $item['class'] ?></div>
                                <div><?= \yii\helpers\Html::a(\Yii::t('app', 'Страница этапа'), $item['url']) ?></div>
							<?php } ?>
                        </div>
                    </div>
				<?php } ?>
			<?php } ?>
        </div>
    </div>

<?= LinkPager::widget([
	'pagination' => $pages,
]); ?>