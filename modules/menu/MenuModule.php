<?php
namespace yii\easyii\modules\menu;

use Yii;

class MenuModule extends \yii\easyii\components\Module
{
    public $settings = [
        'slugImmutable' => false
    ];
    
    public static $installConfig = [
        'title' => [
            'en' => 'Menu',
            'ru' => 'Меню',
        ],
        'icon' => 'menu-hamburger',
        'order_num' => 51,
    ];
}