<?php
namespace yii\easyii\assets;

class WowAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/wowjs/dist';

    public $css = [
    ];
    public $js = [
        'wow.min.js',
    ];

    public $depends = [];
}