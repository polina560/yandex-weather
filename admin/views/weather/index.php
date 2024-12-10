<?php

use admin\components\GroupedActionColumn;
use admin\components\widgets\gridView\Column;
use admin\components\widgets\gridView\ColumnDate;
use admin\modules\rbac\components\RbacHtml;
use admin\widgets\sortableGridView\SortableGridView;
use kartik\grid\SerialColumn;
use yii\httpclient\Client;
use yii\widgets\ListView;

/**
 * @var $this         yii\web\View
 * @var $searchModel  common\models\WeatherSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model        common\models\Weather
 */

$this->title = Yii::t('app', 'Weathers');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="weather-index">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <div>
        <?=
            RbacHtml::a(Yii::t('app', 'Create Weather'), ['create'], ['class' => 'btn btn-success']);
//           $this->render('_create_modal', ['model' => $model]);
        ?>
    </div>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'pjax' => true,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => SerialColumn::class],

            Column::widget(),
            Column::widget(['attr' => 'key']),
            Column::widget(['attr' => 'file', 'format' => 'ntext']),
            ColumnDate::widget(['attr' => 'created_at', 'searchModel' => $searchModel, 'editable' => false]),

            ['class' => GroupedActionColumn::class]
        ]
    ]) ?>

    <?php

    $access_key = '4371045b-5cd6-4956-b194-71885ac091c5';

    $opts = array(
        'http' => array(
            'method' => 'GET',
            'header' => 'X-Yandex-Weather-Key: ' . $access_key
        )
    );

    $context = stream_context_create($opts);

    $file =
        file_get_contents('https://api.weather.yandex.ru/v2/forecast?lat=52.37125&lon=4.89388',
            false, $context);
    echo $file;
    ?>
</div>
