<?php
/**
 * @var \yii\web\View              $this
 * @var \common\models\AssocNews[] $news
 * @var \yii\data\Pagination       $pagination
 */

use yii\widgets\LinkPager;
use yii\helpers\Url;
use yii\bootstrap\Html;

?>

<h2><?= $this->context->pageTitle ?></h2>

<div class="z-100">
    <div class="news">
        <h3>Новости</h3>
		<?php foreach ($news as $item) { ?>
            <div class="item">
                <div class="date"><?= \Yii::$app->formatter->asDate($item->datePublish, "dd.MM.Y") ?></div>
                <div class="preview_text">
					<?= $item->previewText ?>
                </div>
				<?php if ($item->link || $item->fullText) {
					if ($item->link) {
						$link = $item->link;
					} else {
						$link = Url::to(['/site/news', 'id' => $item->id]);
					}
					?>
                    <div class="text-left">
						<?= Html::a('Читать далее ...', $link) ?>
                    </div>
				<?php } ?>
            </div>
		<?php } ?>
    </div>

    <div class="text-left">
		<?= LinkPager::widget(['pagination' => $pagination]) ?>
    </div>
</div>