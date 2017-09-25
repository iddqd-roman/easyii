<?php
/* 
* ADDED BY ROMAN DEVELOPER 
* url for this action is /admin/catalog/items/copy/<id>
*/

namespace yii\easyii\actions;

use Yii;
use yii\easyii\modules\catalog\models\Item;
use yii\easyii\modules\catalog\models\Category;


class CopyAction extends \yii\base\Action
{
    public $model;
    public $successMessage = 'Copied';

    public function run($id)
    {
        $item = Item::findOne($id);
        $category = Category::findOne($item->category_id);
        $item_array = $item->toArray();
        
        $copied_data['Data'] = $item_array['data'];
        unset($item_array['id']);
        unset($item_array['data']);
        $copied_data['Item'] = array_merge(
            $item_array,
            ['time' => time(), 'available' => 1]
        );
        $copied_data['Item']['title'] .= '_copy';
        $copied_data['Item']['slug'] .= '-copy';
        
        $model = new Item();

        if ($model->load($copied_data)) {
            $model->data = $copied_data['Data'];
            if(!$model->save()){
                $this->controller->error = Yii::t('easyii', 'Ошибка копирования');
            }
        }
        else {
            $this->controller->error = Yii::t('easyii', 'Ошибка копирования');
        }
        return $this->controller->formatResponse($this->successMessage);
    }
}