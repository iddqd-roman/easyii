<?php

use yii\db\Migration;
use yii\easyii\modules\news\models\News;
/**
 * Class m170816_154828_source_news
 */
class m170816_154828_source_news extends Migration
{
    public $engine = 'ENGINE=MyISAM DEFAULT CHARSET=utf8';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(News::tableName(), 'source', $this->string(1024));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn(News::tableName(), 'source');
    }
}
