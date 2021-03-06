<?php
use yii\bootstrap\Html;

/**
 * @var int $i
 */
?>
<div class="col-sm-12">
    <hr>
</div>
<div class="col-sm-6 col-xs-12">
    <div class="form-group field-tmpathlete-mark has-success">
        <label class="control-label" for="tmpathlete-mark"><?= \Yii::t('app', 'Марка') ?></label>
		<?= Html::input('text', 'mark[' . $i . ']', null, ['class' => 'form-control', 'placeholder' => \Yii::t('app', 'Марка, напр. kawasaki')]) ?>
    </div>
</div>
<div class="col-sm-6 col-xs-12">
    <div class="form-group field-tmpathlete-model has-success">
        <label class="control-label" for="tmpathlete-model"><?= \Yii::t('app', 'Модель') ?></label>
		<?= Html::input('text', 'model[' . $i . ']', null, ['class' => 'form-control', 'placeholder' => \Yii::t('app', 'Модель, напр. ER6-F')]) ?>
    </div>
</div>
<div class="col-sm-6 col-xs-12">
    <div class="form-group field-tmpathlete-cbm has-success">
        <label class="control-label" for="tmpathlete-cbm">Объём</label>
		<?= Html::input('text', 'cbm[' . $i . ']', null, ['class' => 'form-control', 'placeholder' => \Yii::t('app', 'Объём, см3')]) ?>
    </div>
</div>
<div class="col-sm-6 col-xs-12">
    <div class="form-group field-tmpathletepower has-success">
        <label class="control-label" for="tmpathlete-power">Мощность</label>
		<?= Html::input('text', 'power[' . $i . ']', null, ['class' => 'form-control', 'placeholder' => \Yii::t('app', 'Мощность, л.с.')]) ?>
    </div>
</div>