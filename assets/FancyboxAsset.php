<?php
namespace yii\easyii\assets;

class FancyboxAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/fancybox/source';

    public $css = [
        'jquery.fancybox.css',
        'helpers/jquery.fancybox-thumbs.css'
    ];
    public $js = [
        'jquery.fancybox.pack.js',
        'helpers/jquery.fancybox-thumbs.js'
    ];

    public $depends = ['yii\web\JqueryAsset'];
}