<?php
use yii\helpers\Html;
?>
<p>User <b><?= $post->name ?></b> leaved message in your guestbook.</p>
<p>You can view it <?= Html::a('here', $link) ?>.</p>