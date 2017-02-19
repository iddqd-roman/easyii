<?php

use yii\db\Schema;
use yii\easyii\models;
use \yii\easyii\modules\content\modules\contentElements\models\ElementOption;
use yii\easyii\modules\menu\models\Menu;

class m000009_200004_update extends \yii\db\Migration
{
    public $engine = 'ENGINE=MyISAM DEFAULT CHARSET=utf8';
    
    public function up()
    {
		$this->alterColumn(yii\easyii\modules\catalog\models\Category::tableName(),'description', $this->text());
		$this->alterColumn(yii\easyii\modules\article\models\Category::tableName(),'description', $this->text());
		$this->alterColumn(yii\easyii\modules\gallery\models\Category::tableName(),'description', $this->text());
		$this->alterColumn(yii\easyii\modules\entity\models\Category::tableName(),'description', $this->text());
	}

    public function down()
    {
    }
}
