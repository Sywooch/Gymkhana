<?php
use common\models\Championship;

/**
 * @var \yii\web\View               $this
 * @var \common\models\Championship $championship
 * @var array                       $results
 * @var \common\models\Athlete      $athlete
 * @var integer                     $showAll
 * @var \common\models\Stage[]      $stages
 * @var \common\models\Stage[]      $outOfChampStages
 */
$this->title = 'Результаты: ' . $championship->title;
$this->params['breadcrumbs'][] = ['label' => Championship::$groupsTitle[$championship->groupId], 'url' => ['index', 'groupId' => $championship->groupId]];
$this->params['breadcrumbs'][] = ['label' => $championship->title, 'url' => ['view', 'id' => $championship->id]];
$this->params['breadcrumbs'][] = 'Результаты';
?>

<div class="about">
    Количество этапов, необходимое для участия в чемпионате: <?= $championship->amountForAthlete ?>
    <br>
    Необходимое количество этапов в других регионах:
	<?= $championship->requiredOtherRegions ?>
    <br>
    Количество этапов, по которым ведётся подсчёт результатов:
	<?= $championship->estimatedAmount ?>
</div>

<?php if ($outOfChampStages) { ?>
    <div class="pt-10">
        Этапы вне зачёта:<br>
        <ul>
			<?php foreach ($outOfChampStages as $outOfChampStage) { ?>
                <li><?= $outOfChampStage->title ?></li>
			<?php } ?>
        </ul>
        Баллы за эти этапы не учитываются при подсчёте итоговой суммы. В таблице такие этапы выделены серым цветом.
    </div>
<?php } ?>

<div class="pt-20">
	<?php if ($showAll) { ?>
        В таблице приведены все спортсмены, выступившие хотя бы на одном из этапов, независимо от того, есть ли у них необходимое количество этапов.
        <br>
		<?= \yii\bootstrap\Html::a('Показать участников с необходимым количеством этапов',
			['results', 'championshipId' => $championship->id]) ?>
	<?php } else { ?>
        В таблице приведены только спортсмены, принявшие участие в необходимом количестве этапов.
        <br>
		<?= \yii\bootstrap\Html::a('Показать всех участников',
			['results', 'championshipId' => $championship->id, 'showAll' => true]) ?>
	<?php } ?>
</div>

<table class="table">
    <thead>
    <tr>
        <th>Место</th>
        <th>Класс</th>
        <th>Спортсмен</th>
		<?php foreach ($stages as $stage) {
			$class = '';
			if ($stage->outOfCompetitions) {
				$class = 'gray-column';
			}
			?>
            <th class="<?= $class ?>"><?= $stage->title ?></th>
		<?php } ?>
        <th>Итого</th>
    </tr>
    </thead>
    <tbody>
	<?php
	$place = 0;
	$prevPoints = 0;
	$prevCount = 1;
	foreach ($results as $result) { ?>
		<?php $athlete = $result['athlete'] ?>
		<?php
		if ($result['points'] > 0 && $result['points'] == $prevPoints) {
			$prevCount += 1;
		} else {
			$place += $prevCount;
			$prevCount = 1;
		}
		$prevPoints = $result['points'];
		?>
        <tr>
            <td><?= $place ?></td>
            <td><?= $athlete->athleteClass->title ?></td>
            <td>
				<?= $athlete->getFullName() ?>
                <br>
				<?= $athlete->city->title ?>
            </td>
			<?php foreach ($stages as $stage) {
				$class = '';
				if ($stage->outOfCompetitions) {
					$class = 'gray-column';
				}
				?>
				<?php if (isset($result['stages'][$stage->id])) { ?>
                    <td class="<?= $class ?>"><?= $result['stages'][$stage->id] ?></td>
				<?php } else { ?>
                    <td class="<?= $class ?>">0</td>
				<?php } ?>
			<?php } ?>
            <td><?= $result['points'] ?></td>
        </tr>
	<?php } ?>
    </tbody>
</table>
