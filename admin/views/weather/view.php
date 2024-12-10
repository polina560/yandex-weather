<?php

use admin\components\widgets\detailView\Column;
use admin\modules\rbac\components\RbacHtml;
use common\components\helpers\UserUrl;
use common\models\WeatherSearch;
use yii\widgets\DetailView;

/**
 * @var $this  yii\web\View
 * @var $model common\models\Weather
 */

$this->title = $model->id;
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Weathers'),
    'url' => UserUrl::setFilters(WeatherSearch::class)
];
$this->params['breadcrumbs'][] = RbacHtml::encode($this->title);
?>
<div class="weather-view">

    <h1><?= RbacHtml::encode($this->title) ?></h1>



    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            Column::widget(),
            Column::widget(['attr' => 'key']),
            Column::widget(['attr' => 'json', 'format' => 'ntext']),
            Column::widget(['attr' => 'created_at', 'format' => 'datetime']),
        ]
    ]) ?>

</div>
