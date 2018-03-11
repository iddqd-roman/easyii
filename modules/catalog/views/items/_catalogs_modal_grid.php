<?php
/* @var $data_provider \yii\data\ActiveDataProvider */

use yii\bootstrap\Html;
use yii\grid\GridView;
?>
<?=GridView::widget([
    'dataProvider' => $data_provider,
    'columns' => [
        'id',
        [
            'attribute' => 'title',
            'format'=>'raw',
            'value' => function($data)
            {
                return
                Html::a($data->title, ['/admin/#'], [
                    'onclick' => 'window.catalogFieldLoad(this, \'items\', ' . $data->id  . '); return false;',
                ]);
            }
        ],
    ],
    'tableOptions' => [
        'id' => 'catalog-table',
        'class' => 'table table-striped table-bordered'
    ]
]) ?>