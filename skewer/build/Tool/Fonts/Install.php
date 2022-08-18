<?php

namespace skewer\build\Tool\Fonts;

use skewer\components\config\InstallPrototype;
use skewer\components\design\model\Params;
use skewer\components\fonts\Api;
use yii\helpers\FileHelper;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    public function install()
    {
        Api::createDirectoryDownloadedFonts();

        self::createTable();
    }

    // func

    public function uninstall()
    {
        FileHelper::removeDirectory(Api::getDirPathDownloadedFonts());

        self::dropTable();
        /** Восстановление дефолтного шрифта */
        $oParam = Params::findOne(['name' => 'base.userbase.fontfamily.font-family']);
        $oParam->value = $oParam->default_value;
        $oParam->save();
        \Yii::$app->clearAssets();
    }

    // func

    public static function createTable()
    {
        \Yii::$app->db->createCommand(
            'CREATE TABLE IF NOT EXISTS `fonts` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(100) NOT NULL,
              `fallback` varchar(20) NOT NULL,
              `path` varchar(255) NOT NULL,
              `type` varchar(50) NOT NULL,
              `active` tinyint(1) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;'
        )->execute();

        \Yii::$app->db->createCommand(
            "
            INSERT INTO `fonts` (`name`, `fallback`, `path`, `type`, `active`) VALUES
            ('Lora', 'serif', 'Lora', 'inner', 0),
            ('Open Sans', 'sans-serif', 'Open Sans', 'inner', 0),
            ('Open Sans Condensed', 'sans-serif', 'Open Sans Condensed', 'inner', 0),
            ('PT Sans', 'sans-serif', 'PT Sans', 'inner', 0),
            ('Raleway', 'sans-serif', 'Raleway', 'inner', 0),
            ('Roboto', 'sans-serif', 'Roboto', 'inner', 0),
            ('Montserrat', 'sans-serif', 'Montserrat', 'inner', 0);"
        )->execute();
    }

    public static function dropTable()
    {
        \Yii::$app->db->createCommand('DROP TABLE fonts')->execute();
    }
}//class
