<?php

namespace skewer\build\Adm\Tooltip;

use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\components\config\InstallPrototype;
use skewer\components\i18n\models\ServiceSections;

/**
 * Класс установки для модулей.
 */
class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    // func

    public function install()
    {
        //Создадим таблицу
        \Yii::$app->db->createCommand(
            'CREATE TABLE IF NOT EXISTS `tooltips` (
                  `id` int(10) NOT NULL AUTO_INCREMENT,
                  `name` varchar(255) NOT NULL,
                  `text` text NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;'
        )->execute();
        //Создадим раздел в котором будем хранить файлы

        $iNewPageSection = \Yii::$app->sections->tplNew();

        $aSections = \Yii::$app->sections->getValues('tools');
        foreach ($aSections as $key => $value) {
            $oSection = Tree::addSection($value, \Yii::t('tooltip', 'tooltip_title', [], $key), $iNewPageSection, '', Visible::HIDDEN_NO_INDEX);

            $oServiceSection = new ServiceSections();
            $oServiceSection->name = 'tooltip';
            $oServiceSection->value = $oSection->id;
            $oServiceSection->language = $key;
            $oServiceSection->title = \Yii::t('tooltip', 'tooltip_title', [], $key);

            $oServiceSection->save();

            \Yii::$app->db
                ->createCommand("INSERT INTO `seo_data`(`group`,`row_id`,`none_index`,`none_search`) VALUES('section','{$oServiceSection->id}','1','1')")
                ->execute();
        }

        //return false;
        return true;
    }

    // func

    public function uninstall()
    {
        $aSections = \Yii::$app->sections->getValues('tooltip');
        foreach ($aSections as $key => $value) {
            Tree::removeSection($value);
        }

        return true;
    }

    // func
}// class
