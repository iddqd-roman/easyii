<?php
/**
 * @var \yii\web\View                                                               $this
 * @var \yii\easyii\modules\content\contentElements\standard\heading\models\Element $element
 */

use yii\helpers\Html;

echo Html::tag('h' . $element->number, $element->content);