<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use site\assets\MainPageAsset;

MainPageAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= $this->context->description ?>">
    <meta name="keywords" content="<?= $this->context->keywords ?>">
	<?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->context->pageTitle) ?></title>
	<?php $this->head() ?>
</head>
<body class="aligned sliphover-active">
<?php $this->beginBody() ?>

<!-- =========================
     PRE LOADER
============================== -->
<div class="preloader" id="preloader">

    <!-- ===PAGE LOADER PROGRESS === -->
    <div class="pageloader"></div>

    <!-- === PRE LOADER STATUS === -->
    <div class="status">

        <!-- === pre loader logo === -->
        <div class="logo-preloader">

        </div>

    </div>


</div>

<?= $content ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
