<?php
namespace yii\easyii\assets;

class AnimateAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/animate.css';

    public $css = [
        'animate.min.css',
    ];
    public $js = [
    ];

    public $depends = [];
}