<?php
namespace yii\easyii\behaviors;

use yii\base\Behavior;
use yii\easyii\components\CategoryWithFieldsModel;
use yii\easyii\helpers\Image;
use yii\easyii\helpers\Upload;
use yii\easyii\widgets\DateTimePicker;
use yii\easyii\widgets\GooglePlacesAutoComplete;
use yii\helpers\Html;
use yii\validators\FileValidator;
use yii\web\UploadedFile;

class Fields extends Behavior
{
    public function generateForm($fields, $data = null)
    {
        $result = '';
        foreach($fields as $field)
        {
            $value = !empty($data->{$field->name}) ? $data->{$field->name} : null;
            if ($field->type === CategoryWithFieldsModel::FIELD_TYPE_STRING) {
                $result .= '<div class="form-group"><label>'. $field->title .'</label>'. Html::input('text', "Data[{$field->name}]", $value, ['class' => 'form-control']) .'</div>';
            }
            elseif ($field->type === CategoryWithFieldsModel::FIELD_TYPE_TEXT) {
                $result .= '<div class="form-group"><label>'. $field->title .'</label>'. Html::textarea("Data[{$field->name}]", $value, ['class' => 'form-control']) .'</div>';
            }
            elseif ($field->type === CategoryWithFieldsModel::FIELD_TYPE_HTML) {
                $result .= '<div class="form-group"><label>'. $field->title .'</label>';
                $result .= \yii\easyii\widgets\Redactor::widget([
                    'name' => "Data[{$field->name}]",
                    'value' => $value,
                ]);
                $result .= '</div>';
            }
            elseif ($field->type === CategoryWithFieldsModel::FIELD_TYPE_BOOLEAN) {
                $result .= '<div class="checkbox"><label>'. Html::checkbox("Data[{$field->name}]", $value, ['uncheck' => 0]) .' '. $field->title .'</label></div>';
            }
            elseif ($field->type === CategoryWithFieldsModel::FIELD_TYPE_SELECT) {
                $options = ['' => \Yii::t('easyii', 'Select')];
                foreach($field->options as $option){
                    $options[$option] = $option;
                }
                $result .= '<div class="form-group"><label>'. $field->title .'</label><select name="Data['.$field->name.']" class="form-control">'. Html::renderSelectOptions($value, $options) .'</select></div>';
            }
            elseif ($field->type === CategoryWithFieldsModel::FIELD_TYPE_CHECKBOX) {
                $options = '';
                foreach($field->options as $option){
                    $checked = $value && in_array($option, $value);
                    $options .= '<br><label>'. Html::checkbox("Data[{$field->name}][]", $checked, ['value' => $option]) .' '. $option .'</label>';
                }
                $result .= '<div class="checkbox well well-sm"><b>'. $field->title .'</b>'. $options .'</div>';
            }
            elseif ($field->type === CategoryWithFieldsModel::FIELD_TYPE_FILE) {
                $result .= '<div class="form-group"><label>'. $field->title .'</label><p>';
                if($value != ''){
                    $basename = basename($value);
                    $isImage = preg_match('/\.(jpg|jpeg|png|gif|bmp)$/', $basename);

                    if($isImage) {
                        $result .= Html::a(Html::img(Image::thumb($value, 240, 180)), Upload::getFileUrl($value), ['class' => 'fancybox']);
                    } else {
                        $result .= Html::a($basename, [$value], ['target' => 'blank']);
                    }
                    $result .= ' ' . Html::a($isImage ? \Yii::t('easyii', 'Delete') : '<i class="glyphicon glyphicon-remove"></i>', ['/admin/' . $this->owner->module->id . '/a/delete-data-file', 'file' => $basename], ['class' => 'confirm-delete', 'data-reload' => 1, 'title' => \Yii::t('easyii', 'Delete')]);
                }
                $result .= '</p>' . Html::fileInput("Data[{$field->name}]"). '</div>';
            }
            elseif ($field->type === CategoryWithFieldsModel::FIELD_TYPE_DATE) {
                $result .= '<div class="form-group"><label>'. $field->title .'</label>';
                $result .= DateTimePicker::widget(['name' => "Data[{$field->name}]", 'value' => $value]);
                $result .= '</div>';
            }
            elseif ($field->type === CategoryWithFieldsModel::FIELD_TYPE_ADDRESS) {
                $autocompleteOptions = [];
                if($field->options) {
                    $autocompleteOptions['componentRestrictions'] = ['country' => $field->options];
                }
                $result .= '<div class="form-group"><label>'. $field->title .'</label>';
                $result .= GooglePlacesAutoComplete::widget([
                    'name' => "Data[{$field->name}]",
                    'value' => $value,
                    'options' => ['class' => 'form-control'],
                    'autocompleteOptions' => $autocompleteOptions
                ]);
                $result .= '</div>';
            }
            //Если тип поля == CATALOG, отрисовываем кнопку и всплывашку
            elseif($field->type === CategoryWithFieldsModel::FIELD_TYPE_CATALOG){
                $view_path = dirname(dirname(__FILE__)) .'/modules/catalog/views/items/_catalog_modal.php';
                $result .= '<div class="form-group catalog-field"><label>'. $field->title .'</label>';
                $result .= '<br>' . Html::button('Выбрать', [
                    'class' => 'btn btn-success catalog-choose',
                    'data-toggle' => 'modal',
                    'data-target' => '#modal-' . $field->name,
                ]);
                $result .= \Yii::$app->view->renderFile($view_path, [
                    'id' => $field->name,
                    'title' => $field->title,
                ]);
                $result .= '</div>';
                $result .= '<div id="catalog-field-'.$field->name.'">';
                if(is_array($value)){
                    $items = \yii\easyii\modules\catalog\models\Item::find()->where(['in', 'id', $value])->all();
                    foreach($items as $item){
                        $result .= '<div class="alert alert-info fade in chosen-item" data-id="'.$item->id.'">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                            .$item->fullTitle
                            .'<input type="hidden" name="Data['.$field->name.'][]" value="'.$item->id.'">
                            </div>';
                    }
                }
                $result .= '</div>';
            }
        }
        return $result;
    }

    public function parseData($model)
    {
        $data = \Yii::$app->request->post('Data');

        if(isset($_FILES['Data']))
        {
            foreach($_FILES['Data']['name'] as $fieldName => $sourceName){
                $field = $model->category->getFieldByName($fieldName);
                $validator = new FileValidator(['extensions' => $field->options ? $field->options : null]);
                $uploadInstance = UploadedFile::getInstanceByName('Data['.$fieldName.']');
                if($uploadInstance && $validator->validate($uploadInstance) && ($result = Upload::file($uploadInstance, 'entity', false))) {
                    if(!empty($model->data->{$fieldName})){
                        Upload::delete($model->data->{$fieldName});
                    }
                    $data[$fieldName] = $result;
                } else {
                    $data[$fieldName] = !empty($model->data->{$fieldName}) ? $model->data->{$fieldName} : '';
                }
            }
        }

        return $data;
    }
}