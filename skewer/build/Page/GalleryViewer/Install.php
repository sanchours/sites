<?php

namespace skewer\build\Page\GalleryViewer;

use skewer\base\section\Parameters;
use skewer\base\section\params\Type;
use skewer\components\config\InstallPrototype;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    // func

    public function install()
    {
        // добавление модуля в основной шаблон
        $this->addParameter(\Yii::$app->sections->tplNew(), '.title', 'Галерея на страницу', '', 'GalleryViewer', 'Название для диз. режима');
        $this->addParameter(\Yii::$app->sections->tplNew(), 'object', 'GalleryViewer', '', 'GalleryViewer', 'Объект вывода');
        $this->addParameter(\Yii::$app->sections->tplNew(), 'layout', 'content', '', 'GalleryViewer', 'Область вывода');
        $this->addParameter(\Yii::$app->sections->tplNew(), 'showGallery', '', '', 'GalleryViewer', 'Gallery.gallery_to_show', Type::paramString);
        $this->addParameter(\Yii::$app->sections->tplNew(), 'titleOnMain', 'Галерея', '', 'GalleryViewer', 'Gallery.gallery_main_title', Type::paramString);

        // перекрытие параметров во всех разделах
        foreach (Parameters::getChildrenList(\Yii::$app->sections->tplNew()) as $iSection) {
            $this->setParameter($iSection, 'showGallery', 'GalleryViewer', '', '', 'Gallery.gallery_to_show', Type::paramString);
            $this->setParameter($iSection, 'titleOnMain', 'GalleryViewer', '', '', 'Gallery.gallery_main_title', Type::paramString);
        }

        return true;
    }

    // func

    public function uninstall()
    {
        $this->executeSQLQuery("DELETE FROM `parameters` WHERE `group`='GalleryViewer'");

        return true;
    }

    // func
}//class
