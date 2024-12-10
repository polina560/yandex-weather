<?php

use admin\components\GroupedActionColumn;
use admin\components\widgets\gridView\Column;
use admin\components\widgets\gridView\ColumnDate;
use admin\modules\rbac\components\RbacHtml;
use admin\widgets\sortableGridView\SortableGridView;
use kartik\grid\SerialColumn;
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
            RbacHtml::a(Yii::t('app', 'Create'), ['create'], ['class' => 'btn btn-success']);
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
            Column::widget(['attr' => 'key', 'editable' => false]),
//            Column::widget(['attr' => 'json', 'format' => 'ntext']),
            ColumnDate::widget(['attr' => 'created_at', 'searchModel' => $searchModel, 'editable' => false]),

            ['class' => GroupedActionColumn::class,
                'buttons' => [
                    'update' => function () {return null;},
                    'delete' => function () {return null;}
                ]
            ]
        ]
    ]) ?>
</div>
