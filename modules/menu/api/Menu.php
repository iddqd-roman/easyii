<?php
namespace yii\easyii\modules\menu\api;

use Yii;
use yii\easyii\components\API;
use yii\easyii\helpers\Data;
use yii\helpers\Url;
use yii\easyii\modules\menu\models\Menu as MenuModel;
use yii\helpers\Html;

/**
 * Page module API
 * @package yii\easyii\modules\menu\api
 *
 * @method static string widget($id_slug, array $options = []) Menu widget
 * @method static array items($id_slug) array of all Menu items
 */
class Menu extends \yii\easyii\components\API
{
    private $_items = [];
    public $options = [];

    public function init()
    {
        parent::init();

        $this->_items = Data::cache(MenuModel::CACHE_KEY, 3600, function () {
            return MenuModel::find()->status(MenuModel::STATUS_ON)->asArray()->all();
        });
    }

    public function api_widget($id_slug, $options = [])
    {
        if(count($options)){
            $this->options = array_merge($this->options, $options);
        }
        $this->options['items'] = $this->api_items($id_slug);
        if (!count($this->options['items'])) {
            return LIVE_EDIT
                ? Html::a(Yii::t('easyii/menu/api', 'Create menu'), ['/admin/menu/a/create', 'slug' => $id_slug], ['target' => '_blank'])
                : '';
        }

        $widget = \yii\widgets\Menu::widget($this->options);

        return LIVE_EDIT
            ? API::liveEdit($widget, Url::to(['/admin/menu']), 'div')
            : $widget;
    }

    public function api_items($id_slug)
    {
        if (($menu = $this->findItems($id_slug)) === null) {
            return [];
        }
        return json_decode($menu['items'], true);
    }

    private function findItems($id_slug)
    {
        foreach ($this->_items as $item) {
            if ($item['slug'] == $id_slug || $item['menu_id'] == $id_slug) {
                return $item;
            }
        }
        return null;
    }
}