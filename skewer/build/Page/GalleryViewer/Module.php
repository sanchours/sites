<?php

namespace skewer\build\Page\GalleryViewer;

use skewer\base\site_module;
use skewer\components\gallery;
use skewer\components\GalleryOnPage\Api;

class Module extends site_module\page\ModulePrototype
{
    public $showGallery = 0;
    public $titleOnMain = 'Галерея';

    /** @var string шаблон для слайдера */
    public $template = 'slider.twig';

    /**
     * Метод - исполнитель функционала.
     */
    public function actionIndex()
    {
        if (!$this->showGallery) {
            return psComplete;
        }

        \Yii::$app->router->setLastModifiedDate(gallery\models\Albums::getMaxLastModifyDate());

        $aData = gallery\Photo::getListWithSeoData(explode(',', $this->showGallery), $this->sectionId(), true);

        $this->setData('list', $aData);
        $this->setData('titleOnMain', $this->titleOnMain);
        $this->setData('labelModule', $this->getLabel());
        // ресурсы подключаются только если нужен вывод
        // при переводе на php шаблонны должно быть перенесено в них
        Asset::register(\Yii::$app->view);

        $this->setData('gallerySettings', Api::getSettingsByEntity('Page', true));

        $this->setTemplate($this->template);

        return psComplete;
    }

    /**
     * {@inheritdoc}
     */
    public function autoInitAsset()
    {
        return false;
    }
}
