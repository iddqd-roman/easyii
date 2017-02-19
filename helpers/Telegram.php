<?php
namespace yii\easyii\helpers;

use Yii;
use yii\base\InvalidConfigException;
use yii\easyii\models\Setting;

class Telegram
{
    /**
     * Отправка сообщений в чат, используя бота
     * @param string $botToken
     * @param string $chatID
     * @param string $template
     * @param array $data
     * @return bool|string
     */
    public static function send($botToken, $chatID, $template, $data = [])
    {

        if(!$template){
            return false;
        }

        $message = \Yii::$app->view->renderFile($template.'.php', $data);

        return file_get_contents('https://api.telegram.org/bot'.$botToken.'/sendMessage?chat_id='.$chatID.'&parse_mode=HTML&text='.$message);
    }
}