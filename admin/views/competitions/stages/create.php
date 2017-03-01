<?php
/**
 * @var \yii\web\View        $this
 * @var \common\models\Stage $model
 * @var int                  $success
 * @var int                  $errorCity
 */

$this->title = 'Создание нового этапа';
$this->params['breadcrumbs'][] = ['label' => 'Чемпионаты', 'url' => ['/competitions/championships/index']];
$this->params['breadcrumbs'][] = ['label' => $model->championship->title, 'url' => ['/competitions/championships/view', 'id' => $model->championshipId]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stage-create">
	
	<?= $this->render('//competitions/common/_city-form', ['errorCity' => $errorCity, 'success' => $success, 'actionType' => 'withId']) ?>

    <hr>

    <h3>Создать этап</h3>
    <div class="alert alert-info">Рекомендуем сначала проверить, есть ли необходимый город в списке</div>
	<?= $this->render('_form', [
		'model' => $model,
	]) ?>

</div>