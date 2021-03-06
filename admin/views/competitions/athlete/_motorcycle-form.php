<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $motorcycle common\models\Motorcycle */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="athlete-form">
	<?php $form = ActiveForm::begin(); ?>
	
	<?= $form->field($motorcycle, 'athleteId')->hiddenInput()->label(false)->error(false) ?>
	<div class="row">
        <div class="col-md-5 col-sm-4">
			<?= $form->field($motorcycle, 'mark')->textInput(['placeholder' => 'марка, напр. kawasaki'])->label(false) ?>
        </div>
        <div class="col-md-5 col-sm-4">
			<?= $form->field($motorcycle, 'model')->textInput(['placeholder' => 'модель, напр. ER6-F'])->label(false) ?>
        </div>
        <div class="col-md-4 col-sm-3">
			<?= $form->field($motorcycle, 'cbm')->textInput(['placeholder' => 'Объём двигателя'])->label(false) ?>
        </div>
        <div class="col-md-4 col-sm-3">
			<?= $form->field($motorcycle, 'power')->textInput(['placeholder' => 'Мощность'])->label(false) ?>
        </div>
        <div class="col-md-2 col-sm-2">
			<?= $form->field($motorcycle, 'isCruiser')->checkbox()->label(false) ?>
        </div>
		<div class="col-md-2 col-sm-4">
			<div class="form-group complete">
				<?= Html::submitButton('Добавить', ['class' => 'btn btn-my-style btn-green']) ?>
			</div>
		</div>
	</div>
	
	<?php ActiveForm::end(); ?>

</div>
