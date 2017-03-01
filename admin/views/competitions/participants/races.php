<?php
use yii\bootstrap\ActiveForm;
use kartik\widgets\TimePicker;
use yii\widgets\MaskedInput;
use yii\bootstrap\Html;

/**
 * @var \yii\web\View              $this
 * @var \common\models\Stage       $stage
 * @var \common\models\Participant $participant
 * @var string                     $error
 */

$this->title = $stage->championship->title . ', ' . $stage->title;

$attempt = 0;
?>

<?php if ($error) { ?>
    <div class="alert alert-danger">
        Не установлены классы спортсменов
	    <?= Html::a('Установить', ['/competitions/stages/calculation-result', 'stageId' => $stage->id],
		    [
			    'class'   => 'btn btn-default setParticipantsClasses',
			    'data-id' => $stage->id
		    ]) ?>
    </div>
<?php } ?>

<?php while ($attempt++ < $stage->countRace) { ?>
    <h3>Заезд №<?= $attempt ?></h3>
    <div class="row">
        <div class="col-sm-1"><b>№ участника</b></div>
        <div class="col-sm-3"><b>ФИО</b></div>
        <div class="col-sm-3"><b>Мотоцикл</b></div>
        <div class="col-sm-2"><b>Время</b></div>
        <div class="col-sm-1"><b>Штраф</b></div>
        <div class="col-sm-1"></div>
    </div>
    <hr>
	<?php foreach ($stage->activeParticipants as $participant) {
		$timeModel = $participant->getTimeForm($attempt);
		?>
		<?php $form = ActiveForm::begin([
			'id'      => 'raceTimeForm' . $participant->id,
			'options' => [
				'class' => 'raceTimeForm',
			]
		
		]); ?>
        <div class="row">
			<?= $form->field($timeModel, 'stageId')->hiddenInput()->label(false)->error(false) ?>
			<?= $form->field($timeModel, 'participantId')->hiddenInput()->label(false)->error(false) ?>
			<?php if ($timeModel->id) { ?>
				<?= $form->field($timeModel, 'id')->hiddenInput()->label(false)->error(false) ?>
			<?php } ?>
            <div class="col-sm-1"><?= $participant->number ?></div>
            <div class="col-sm-3"><?= $participant->athlete->getFullName() ?></div>
            <div class="col-sm-3"><?= $participant->motorcycle->getFullTitle() ?></div>
            <div class="col-sm-2">
				<?= $form->field($timeModel, 'timeForHuman')->widget(MaskedInput::classname(), [
					'mask'    => '99:99.99',
					'options' => [
						'id'    => 'timeForHuman' . $participant->id . '-' . $attempt,
						'class' => 'form-control'
					]
				])->label(false) ?>
            </div>
            <div class="col-sm-1"><?= $form->field($timeModel, 'fine')->textInput()->label(false) ?></div>
            <div class="col-sm-1">
                <button type="submit" class="btn btn-primary btn-circle fa fa-save" title="Сохранить"></button>
            </div>
        </div>
		<?php $form->end(); ?>
		<?php
	} ?>
<?php } ?>