<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use dosamigos\ckeditor\CKEditor;

/* @var $this yii\web\View */
/* @var $model common\models\AboutBlock */

$this->title = 'Контактная информация';
$this->params['breadcrumbs'][] = ['label' => 'О проекте'];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="contacts">

	<?php $form = ActiveForm::begin(); ?>

	<?= $form->field($model, 'phone')->textarea(['rows' => 6]) ?>

	<?= $form->field($model, 'email')->textInput() ?>

	<?= $form->field($model, 'addr')->textarea(['rows' => 6]) ?>

	<?= $form->field($model, 'time')->widget(CKEditor::className(), [
		'preset'  => 'basic'
	]) ?>

	<?= $form->field($model, 'card')->textInput() ?>

	<?= $form->field($model, 'cardInfo')->textarea(['rows' => 6]) ?>

	<div class="form-group">
		<?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>
