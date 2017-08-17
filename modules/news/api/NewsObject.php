<?php
namespace yii\easyii\modules\news\api;

use Yii;
use yii\easyii\components\API;
use yii\easyii\models\Photo;
use yii\easyii\modules\news\models\News as NewsModel;
use yii\helpers\Url;

class NewsObject extends \yii\easyii\components\ApiObject
{
    /** @var  string */
    public $slug;
    /** @var  string */
    public $source;
    public $views;
    public $time;

    private $_photos;

    public function getTitle($liveEditable = true){
        return ($liveEditable && LIVE_EDIT_ENABLED) ? API::liveEdit($this->model->title, $this->getEditLink()) : $this->model->title;
    }

    public function getShort(){
        return LIVE_EDIT_ENABLED ? API::liveEdit($this->model->short, $this->getEditLink()) : $this->model->short;
    }

    public function getText(){
        return LIVE_EDIT_ENABLED ? API::liveEdit($this->model->text, $this->getEditLink(), 'div') : $this->model->text;
    }

    public function getTags(){
        return $this->model->tagsArray;
    }

    public function getDate(){
        return Yii::$app->formatter->asDate($this->time);
    }

    public function getPhotos()
    {
        if(!$this->_photos){
            $this->_photos = [];

            foreach(Photo::find()->where(['class' => NewsModel::className(), 'item_id' => $this->id])->sort()->all() as $model){
                $this->_photos[] = new PhotoObject($model);
            }
        }
        return $this->_photos;
    }

    public function  getEditLink(){
        return Url::to(['/admin/news/a/edit/', 'id' => $this->id]);
    }

    /**
     * Return max length chars from source
     * If $length == 0, return without truncate
     * @param int $length
     * @return bool|string
     */
    public function getSource($length = 0){
        if ($length){
            return substr($this->source, 0, $length);
        }
        return $this->source;
    }
}