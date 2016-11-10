<?php

use yii\db\Schema;
use yii\easyii\models;
use \yii\easyii\modules\content\modules\contentElements\models\ElementOption;
use yii\easyii\modules\menu\models\Menu;

class m000009_200003_module_menu extends \yii\db\Migration
{
    public $engine = 'ENGINE=MyISAM DEFAULT CHARSET=utf8';
    
    public function up()
    {
		//CAROUSEL MODULE
		$this->createTable(Menu::tableName(), [
			'menu_id' => $this->primaryKey(),
			'slug' => $this->string(128),
			'title' => $this->string(255),
			'items' => $this->text(),
			'status' => $this->boolean()->defaultValue(1),
		], $this->engine);

	}

    public function down()
    {
		$this->dropTable(ElementOption::tableName());
    }
}
