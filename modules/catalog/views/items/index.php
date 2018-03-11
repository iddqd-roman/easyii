<?php
use yii\easyii\modules\catalog\models\Item;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('easyii/catalog', 'Catalog');

$module = $this->context->module->id;
?>
<?= $this->render('_menu', ['category' => $category]) ?>

<?php if(count($items)) : ?>
    <table class="table table-hover">
        <thead>
        <tr>
            <th width="50">
                <?=Html::a('#', [
                    '/admin/'.$module.'/items/sort',
                    'field' => 'id',
                    'direction' => ($sort[0] == 'id' && $sort[1] == SORT_ASC ? SORT_DESC : SORT_ASC)
                ]);?>
            </th>
            <th>
                <?=Html::a(Yii::t('easyii', 'Name'), [
                    '/admin/'.$module.'/items/sort',
                    'field' => 'fulltitle',
                    'direction' => ($sort[0] == 'fulltitle' && $sort[1] == SORT_ASC ? SORT_DESC : SORT_ASC)
                ]);?>
            </th>
            <th width="100"><?= Yii::t('easyii', 'Status') ?></th>
            <th width="160"></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($items as $item) : ?>
            <tr data-id="<?= $item->primaryKey ?>">
                <td><?= $item->primaryKey ?></td>
                <td>
                    <a href="<?= Url::to(['/admin/'.$module.'/items/edit', 'id' => $item->primaryKey]) ?>">
                        <?=$item->fulltitle; ?>
                    </a>
                </td>
                <td class="status">
                    <?= Html::checkbox('', $item->status == Item::STATUS_ON, [
                        'class' => 'switch',
                        'data-id' => $item->primaryKey,
                        'data-link' => Url::to(['/admin/'.$module.'/items']),
                    ]) ?>
                </td>
                <td class="text-right">
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="<?= Url::to(['/admin/'.$module.'/items/copy', 'id' => $item->primaryKey]) ?>" class="btn btn-default confirm-copy" title="Копировать"><strong>x2</strong></a>
                        <a href="<?= Url::to(['/admin/'.$module.'/items/up', 'id' => $item->primaryKey, 'category_id' => $category->primaryKey]) ?>" class="btn btn-default move-up" title="<?= Yii::t('easyii', 'Move up') ?>"><span class="glyphicon glyphicon-arrow-up"></span></a>
                        <a href="<?= Url::to(['/admin/'.$module.'/items/down', 'id' => $item->primaryKey, 'category_id' => $category->primaryKey]) ?>" class="btn btn-default move-down" title="<?= Yii::t('easyii', 'Move down') ?>"><span class="glyphicon glyphicon-arrow-down"></span></a>
                        <a href="<?= Url::to(['/admin/'.$module.'/items/delete', 'id' => $item->primaryKey]) ?>" class="btn btn-default confirm-delete" title="<?= Yii::t('easyii', 'Delete item') ?>"><span class="glyphicon glyphicon-remove"></span></a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <style>.confirm-copy{line-height:12px !important;}</style>
<?php else : ?>
    <p><?= Yii::t('easyii', 'No records found') ?></p>
<?php endif; ?>