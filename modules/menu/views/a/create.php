<?php
$this->title = Yii::t('easyii/menu', 'Create menu');
?>
<?= $this->render('_menu') ?>
<?= $this->render('_form', ['model' => $model]) ?>