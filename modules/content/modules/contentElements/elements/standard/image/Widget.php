<?php

namespace yii\easyii\modules\content\modules\contentElements\elements\standard\image;

use yii\easyii\helpers\Image;
use yii\easyii\modules\content\modules\contentElements\BaseWidget;
use yii\easyii\modules\content\modules\contentElements\elements\standard\image\models\Element;
use yii\web\UploadedFile;

/**
 * Class Widget
 *
 * @property Element $element
 *
 * @author Bennet Klarhoelter <boehsermoe@me.com>
 */
class Widget extends BaseWidget
{
	public function beforeSave()
	{
		$model = $this->element;
		
		if (isset($_FILES) && \Yii::$app->controller->module->settings['itemThumb']) {

			$model->source = UploadedFile::getInstance($model, 'source');
			var_dump($model->source, $model->attribute);die;
			if ($model->source && $model->validate(['source'])) {
				$model->source = Image::upload($model->source, 'content');
			} else {
				$model->source = $model->oldAttributes['source'];
			}

		}
	}
}