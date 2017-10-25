<?php
/**
 * @var \common\models\SpecialStage $model
 * @var string                      $token
 * @var string                      $language
 */
$championship = $model->championship;
?>
    <b>
		<?= \Yii::t('app', 'Анонсирован новый этап чемпионата {champTitle}: "{stageTitle}"!', [
			'champTitle' => $championship->title,
			'stageTitle' => '<a href="http://gymkhana-cup.ru/competitions/special-stage?id="' . $model->id
				. 'target="_blank" style="color: #56a025">' . $model->title . '</a>'
		], $language) ?>
    </b>
    <br><br>

<?php if ($model->description) { ?>
	<?= $model->description ?><br>
<?php } ?>

<?php if ($model->dateStart) { ?>
    <br><?= \Yii::t('app', 'Приём результатов начнётся {date}.', ['date' => $model->dateStartHuman], $language) ?>
<?php } ?>

<?php if ($model->dateResult) { ?>
    <br><?= \Yii::t('app', 'Результаты будут опубликованы {date}.', ['date' => $model->dateResultHuman], $language) ?>
<?php } ?>

    <br><br>


<?php if ($model->photoPath) { ?>
	<?= \Yii::t('app', 'Подробнее о этапе вы можете узнать на сайте {site}. Там же можно посмотреть фото трассы.', [
		'site' => '<a href="http://gymkhana-cup.ru/competitions/special-stage?id=' . $model->id . '" target="_blank" style="color: #56a025">gymkhana-cup.ru</a>.&nbsp;'
	], $language) ?>
<?php } else { ?>
	<?= \Yii::t('app', 'Подробнее о этапе вы можете узнать на сайте {site}. Фотография трассы будет опубликована позже на том же сайте.', [
		'site' => '<a href="http://gymkhana-cup.ru/competitions/special-stage?id=' . $model->id . '" target="_blank" style="color: #56a025">gymkhana-cup.ru</a>.&nbsp;'
	], $language) ?>
<?php } ?>