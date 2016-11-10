<?php
namespace yii\easyii\modules\menu\models;

use Yii;
use yii\behaviors\SluggableBehavior;
use yii\easyii\behaviors\CacheFlush;
use yii\easyii\behaviors\SortableModel;
use yii\easyii\modules\menu\MenuModule;

class Menu extends \yii\easyii\components\ActiveRecord
{
    const STATUS_OFF = 0;
    const STATUS_ON = 1;
    const CACHE_KEY = 'easyii_menu';

    public static function tableName()
    {
        return 'easyii_menu';
    }

    public function rules()
    {
        return [
            ['title', 'required'],
            ['title', 'string', 'max' => 128],
            [['title', 'items'], 'trim'],
            ['slug', 'match', 'pattern' => self::$SLUG_PATTERN, 'message' => Yii::t('easyii', 'Slug can contain only 0-9, a-z and "-" characters (max: 128).')],
            ['slug', 'default', 'value' => null],
            ['items', 'default', 'value' => '[]'],
            ['slug', 'unique'],
            ['status', 'integer'],
            ['status', 'default', 'value' => self::STATUS_ON],
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => Yii::t('easyii', 'Title'),
            'slug' => Yii::t('easyii', 'Slug'),
            'items' => Yii::t('easyii', 'Items'),
        ];
    }

    public function behaviors()
    {
        return [
            CacheFlush::className(),
            [
                'class' => SluggableBehavior::className(),
                'attribute' => 'title',
                'ensureUnique' => true,
                'immutable' => MenuModule::setting('slugImmutable')
            ],
        ];
    }
}