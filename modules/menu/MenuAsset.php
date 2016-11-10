<?php
namespace yii\easyii\modules\menu;

use yii\web\AssetBundle;

class MenuAsset extends AssetBundle{
    public $sourcePath = '@easyii/modules/menu/assets';
    public $baseUrl = '@web';
    public $js = [
        'js/Sortable.min.js',
        'js/menu.js',
    ];
    public $css = [
        'css/menu.css',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
}
