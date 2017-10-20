<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\DateTimePicker;
use dosamigos\ckeditor\CKEditor;

/* @var $this yii\web\View */
/* @var $model common\models\SpecialStage */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="stage-form">
	<?php $form = ActiveForm::begin(); ?>
	
	<?= $form->field($model, 'championshipId')->hiddenInput()->label(false)->error(false) ?>
	
	<?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
	
	<?= $form->field($model, 'outOfCompetitions')->checkbox() ?>
	
	<?= $form->field($model, 'description')->widget(CKEditor::className(), [
		'preset' => 'full', 'clientOptions' => ['height' => 150]
	]) ?>
	
	<?= $form->field($model, 'dateStartHuman',
		['inputTemplate' => '<div class="input-with-description"><div class="text">Обратите внимание - время Московское!</div>{input}</div>'])
		->widget(DateTimePicker::classname(), [
			'options'       => ['placeholder' => 'Введите дату и время начала приёма результатов'],
			'removeButton'  => false,
			'language'      => 'ru',
			'pluginOptions' => [
				'autoclose' => true,
				'format'    => 'dd.mm.yyyy, hh:ii',
			]
		]) ?>
	
	<?= $form->field($model, 'dateEndHuman',
		['inputTemplate' => '<div class="input-with-description"><div class="text">Обратите внимание - время Московское!</div>{input}</div>'])
		->widget(DateTimePicker::classname(), [
			'options'       => ['placeholder' => 'Введите дату и время завершения приёма результатов'],
			'removeButton'  => false,
			'language'      => 'ru',
			'pluginOptions' => [
				'autoclose' => true,
				'format'    => 'dd.mm.yyyy, hh:ii',
			]
		]) ?>
	
	<?= $form->field($model, 'dateResultHuman')->widget(DatePicker::classname(), [
		'options'       => ['placeholder' => 'Введите дату подсчёта итогов'],
		'removeButton'  => false,
		'language'      => 'ru',
		'pluginOptions' => [
			'autoclose' => true,
			'format'    => 'dd.mm.yyyy',
		]
	]) ?>
	
	<?php if ($model->photoPath) { ?>
        <div class="row">
            <div class="col-md-2 col-sm-4 img-in-profile">
				<?= Html::img(\Yii::getAlias('@filesView') . '/' . $model->photoPath) ?>
                <br>
                <a href="#" class="btn btn-default btn-block deletePhoto" data-id="<?= $model->id ?>"
                   data-model="<?= \admin\controllers\competitions\HelpController::PHOTO_SPECIAL_STAGE ?>">удалить</a>
                <br>
            </div>
            <div class="col-md-10 col-sm-8">
				<?= $form->field($model, 'photoFile', ['inputTemplate' => '<div class="input-with-description"><div class="text">
 Допустимые форматы: png, jpg. Максимальный размер: 2МБ.
</div>{input}</div>'])->fileInput(['multiple' => false, 'accept' => 'image/*']) ?>
            </div>
        </div>
	<?php } else { ?>
		<?= $form->field($model, 'photoFile', ['inputTemplate' => '<div class="input-with-description"><div class="text">
 Допустимые форматы: png, jpg. Максимальный размер: 2МБ.
</div>{input}</div>'])->fileInput(['multiple' => false, 'accept' => 'image/*']) ?>
	<?php } ?>
	
	<?= $form->field($model, 'classId',
		['inputTemplate' => '<div class="input-with-description"><div class="text">
 Это поле заполняется автоматически после завершения регистрации. Меняйте его только в том случае, если рассчитанный класс
        не будет соответствовать действительности. Если этап ещё не начался - оставьте это поле пустым.
</div>{input}</div>'])
		->dropDownList(\yii\helpers\ArrayHelper::map(
			\common\models\AthletesClass::find()->andWhere(['status' => \common\models\AthletesClass::STATUS_ACTIVE])->orderBy(['sort' => SORT_ASC])->all(), 'id', 'title'
		), ['prompt' => 'Укажите класс']) ?>
	
	<?= $form->field($model, 'status')->dropDownList(\common\models\Stage::$statusesTitle) ?>
    <div class="form-group">
		<?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить',
			['class' => $model->isNewRecord ? 'btn btn-my-style btn-green' : 'btn btn-my-style btn-blue']) ?>
    </div>
	
	<?php ActiveForm::end(); ?>

</div>
