<?php
/* @var $data_provider \yii\data\ActiveDataProvider */

use yii\bootstrap\Html;
use yii\grid\GridView;
?>
<div>
    <?=Html::button('Назад', [
        'onclick' => 'catalogFieldLoad(this, \'categories\')',
        'class' => 'btn btn-info'
    ]);?>
</div>
<?=GridView::widget([
    'dataProvider' => $data_provider,
    'columns' => [
        [
            'attribute' => 'id',
            'header' => '<a href="#" onclick="catalogFieldSort(0, true);return false;">#</a>'
        ],
        [
            'attribute' => 'fullTitle',
            'header' => '<a href="#" onclick="catalogFieldSort(1);return false;">Название</a>'
        ],
        [
            'header' => '',
            'content' => function($data){
                return Html::checkbox('id[]', false, [
                    'data-id' => $data->id,
                    'onclick' => 'window.catalogFieldAddItem(this, \''.$data->id.'\', \''.$data->getFullTitle().'\');',
                ]);
            },
        ]
    ],
    'tableOptions' => [
        'id' => 'catalog-table',
        'class' => 'table table-striped table-bordered'
    ]
]) ?>