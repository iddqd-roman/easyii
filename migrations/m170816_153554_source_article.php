<?php

use yii\db\Migration;
use yii\easyii\modules\article;

/**
 * Class m170816_153554_source_article
 */
class m170816_153554_source_article extends Migration
{
    public $engine = 'ENGINE=MyISAM DEFAULT CHARSET=utf8';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(article\models\Item::tableName(), 'source', $this->string(1024));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn(article\models\Item::tableName(), 'source');
    }
}