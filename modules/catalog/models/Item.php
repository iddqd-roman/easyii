<?php
namespace yii\easyii\modules\catalog\models;

use Yii;
use yii\easyii\behaviors\ImageFile;
use yii\easyii\behaviors\JsonColumns;
use yii\easyii\behaviors\SeoBehavior;
use yii\easyii\behaviors\SlugBehavior;
use yii\easyii\models\Photo;
use yii\easyii\modules\catalog\CatalogModule;

class Item extends \yii\easyii\components\ActiveRecord
{
    const STATUS_OFF = 0;
    const STATUS_ON = 1;
    
    private $_full_title;
    
    public static function tableName()
    {
        return 'easyii_catalog_items';
    }

    public function rules()
    {
        return [
            ['title', 'required'],
            ['title', 'trim'],
            ['title', 'string', 'max' => 128],
            ['image_file', 'image'],
            ['description', 'safe'],
            ['price', 'number'],
            ['discount', 'integer', 'max' => 99],
            [['status', 'category_id', 'available', 'time'], 'integer'],
            ['time', 'default', 'value' => time()],
            ['slug', 'match', 'pattern' => self::$SLUG_PATTERN, 'message' => Yii::t('easyii', 'Slug can contain only 0-9, a-z and "-" characters (max: 128).')],
            ['slug', 'default', 'value' => null],
            ['available', 'default', 'value' => 1],
            ['status', 'default', 'value' => self::STATUS_ON],
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => Yii::t('easyii', 'Title'),
            'category_id' => Yii::t('easyii', 'Category'),
            'image_file' => Yii::t('easyii', 'Image'),
            'description' => Yii::t('easyii', 'Description'),
            'available' => Yii::t('easyii/catalog', 'Available'),
            'price' => Yii::t('easyii/catalog', 'Price'),
            'discount' => Yii::t('easyii/catalog', 'Discount'),
            'time' => Yii::t('easyii', 'Date'),
            'slug' => Yii::t('easyii', 'Slug'),
        ];
    }

    public function behaviors()
    {
        $behaviors = [
            'seoBehavior' => SeoBehavior::className(),
            'sluggable' => [
                'class' => SlugBehavior::className(),
                'immutable' => CatalogModule::setting('itemSlugImmutable')
            ],
            'jsonColumns' => [
                'class' => JsonColumns::className(),
                'columns' => ['fields', 'data']
            ],
        ];
        if(CatalogModule::setting('itemThumb')){
            $behaviors['imageFileBehavior'] = ImageFile::className();
        }
        return $behaviors;
    }

    public function afterSave($insert, $attributes){
        parent::afterSave($insert, $attributes);

        ItemData::deleteAll(['item_id' => $this->primaryKey]);

        foreach($this->data as $name => $value){
            if(!is_array($value)){
                $this->insertDataValue($name, $value);
            } else {
                foreach($value as $arrayItem){
                    $this->insertDataValue($name, $arrayItem);
                }
            }
        }
    }

    private function insertDataValue($name, $value){
        Yii::$app->db->createCommand()->insert(ItemData::tableName(), [
            'item_id' => $this->primaryKey,
            'name' => $name,
            'value' => $value
        ])->execute();
    }

    public function getPhotos()
    {
        return $this->hasMany(Photo::className(), ['item_id' => 'id'])->where(['class' => self::className()])->sort();
    }

    public function getCategory()
    {
        return Category::get($this->category_id);
    }

    public function afterDelete()
    {
        parent::afterDelete();

        foreach($this->getPhotos()->all() as $photo){
            $photo->delete();
        }

        ItemData::deleteAll(['item_id' => $this->primaryKey]);
    }
    
    
    public function getFullTitle($middle_separator = '')
    {
        if($this->_full_title === null){
            if(!isset($this->data->width, $this->data->height, $this->data->diameter, $this->data->brand)){
                return $this->title;
            }
            //ЕСЛИ ДИСКИ
            $cat_slug = Category::findOne($this->category_id)->slug;
            if($cat_slug == 'wheels'){
                $brand = isset($this->data->brand) ? $this->data->brand : '';
                $width = isset($this->data->width) ? $this->data->width : '';
                $diameter = isset($this->data->diameter) ? $this->data->diameter : '';
                $fixture = isset($this->data->fixture) ? $this->data->fixture : '';
                $pcd = isset($this->data->pcd) ? $this->data->pcd : '';
                $dia = isset($this->data->dia) ? $this->data->dia : '';
                $et = isset($this->data->et) ? $this->data->et : '';

                $param_1 = implode('x', [$width, $diameter]);
                $param_2 = implode('/', [$param_1, $fixture . 'x' . $pcd, $dia, $et]);
                $middle_separator = ($middle_separator == '') ? ' ' : $middle_separator;

                return $brand . $middle_separator . $param_2;
            }
            else{
                //ЕСЛИ ШИНЫ
                $width = isset($this->data->width) ? $this->data->width : '';
                $height = isset($this->data->height) ? $this->data->height : '';
                $diameter = isset($this->data->diameter) ? $this->data->diameter : '';
                $brand = isset($this->data->brand) ? $this->data->brand : '';
                $title = $this->title;

                $params = [];
                if(!empty($width)){
                    $params[] = $width;
                }
                if(!empty($height)){
                    $params[] = $height;
                }
                $three_params = implode('/', $params);
                $three_params .= $diameter ? 'R' . $diameter : '';
                $three_params .= $middle_separator;

                $this->_full_title = implode(' ', [
                    $three_params,
                    $brand,
                    $title,
                ]);
            }
        }
        return $this->_full_title;
    }
}