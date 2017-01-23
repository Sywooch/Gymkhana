<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use site\assets\PagesAsset;

PagesAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= $this->context->description ?>">
    <meta name="keywords" content="<?= $this->context->keywords ?>">
    <link rel="icon" type="image/x-icon" href="/favicon.ico" sizes="64x64">
	<?= Html::csrfMetaTags() ?>
    <title>Мотоджимхана: <?= Html::encode($this->context->pageTitle) ?></title>
	<?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<?= $this->render('_page', ['content' => $content]) ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
