<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\NoticesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Уведомления для зарегистрировавшихся пользователей';
?>
<div class="notice-index">

    <p>
        <?= Html::a('Отправить уведомление', ['create'], ['class' => 'btn btn-my-style btn-green']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            
            'text',
        ],
    ]); ?>
</div>
