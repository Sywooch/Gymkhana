<?php
/**
 * @var \yii\web\View                 $this
 * @var \common\models\SpecialChamp   $championship
 * @var array                         $results
 * @var \common\models\SpecialStage[] $stages
 * @var \common\models\Athlete        $athlete
 * @var \common\models\SpecialStage[] $outOfChampStages
 */
$this->title = 'Результаты: ' . $championship->title;
?>
<h3><?= $this->title ?></h3>

<div class="about pt-10">
    <h4>Таблица, по которой прозводился расчёт баллов за каждый этап:</h4>
    <table class="table table-responsive table-bordered text-center">
        <tr>
            <td class="text-left"><b>место</b></td>
			<?php foreach (\common\models\SpecialStage::$points as $place => $point) { ?>
                <td><?= $place ?></td>
			<?php } ?>
        </tr>
        <tr>
            <td class="text-left"><b>балл</b></td>
			<?php foreach (\common\models\SpecialStage::$points as $place => $point) { ?>
                <td><?= $point ?></td>
			<?php } ?>
        </tr>
    </table>
	
	<?php if ($outOfChampStages) { ?>
        <div class="pt-10 pb-10">
            <b>Следующие этапы проводились вне зачёта:</b><br>
            <ul>
				<?php foreach ($outOfChampStages as $outOfChampStage) { ?>
                    <li><?= $outOfChampStage->title ?></li>
				<?php } ?>
            </ul>
            Баллы за эти этапы не учитываются при подсчёте итоговой суммы. В таблице такие этапы выделены серым цветом.
        </div>
	<?php } ?>
</div>

<div class="pt-10">
    <h4>Результаты:</h4>
    <table class="table table-responsive">
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
                <th class="<?= $class ?>"><?= \yii\helpers\Html::a($stage->title, ['/competitions/special-stage', 'id' => $stage->id]) ?></th>
			<?php } ?>
            <th>Итого</th>
        </tr>
        </thead>
        <tbody>
		<?php
		$place = 0;
		$prevPoints = 0;
		$prevCount = 1;
		foreach ($results as $result) {
			?>
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
                <td><?= $athlete->athleteClassId ? $athlete->athleteClass->title : null ?></td>
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
</div>