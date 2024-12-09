<?php

use common\components\helpers\UserUrl;
use common\models\WeatherSearch;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $model common\models\Weather
 */

$this->title = Yii::t('app', 'Create Weather');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Weathers'),
    'url' => UserUrl::setFilters(WeatherSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="weather-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model, 'isCreate' => true]) ?>

</div>
