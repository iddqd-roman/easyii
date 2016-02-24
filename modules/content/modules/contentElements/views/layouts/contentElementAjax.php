<?php
/**
 * @var \yii\web\View                                           $this
 * @var $content
 * @var \yii\easyii\modules\content\modules\contentElements\BaseElement $element
 */
use yii\helpers\Html;

?>
<?php $this->beginPage() ?>

<tr data-element-id="<?= $element->primaryKey ?>" data-element-type="<?= $element->type ?>" data-element-scenario="<?= $element->scenario ?>" >

	<?php $this->head() ?>

	<?php $this->beginBody() ?>

	<td>
		<?= Html::tag('h4', $config['title']) ?>

		<?= Html::errorSummary($element, ['class' => 'alert alert-danger']) ?>

		<?= Html::activeHiddenInput($element, 'element_id'); ?>
		<?= Html::activeHiddenInput($element, 'type'); ?>
		<?= Html::activeHiddenInput($element, 'scenario'); ?>

		<?= $content ?>
	</td>
	<td class="text-right">
		<div class="btn-group btn-group-sm" role="group">
			<a href="#" class="btn btn-default move-up" title="'. Yii::t('easyii', 'Move up') .'"><span class="glyphicon glyphicon-arrow-up"></span></a>
			<a href="#" class="btn btn-default move-down" title="'. Yii::t('easyii', 'Move down') .'"><span class="glyphicon glyphicon-arrow-down"></span></a>
			<a href="#" class="btn btn-default color-red delete-element" title="'. Yii::t('easyii', 'Delete item') .'"><span class="glyphicon glyphicon-remove"></span></a>
		</div>
	</td>

	<?php $this->endBody() ?>

</tr>

<?php $this->endPage(true) ?>
