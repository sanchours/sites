<?php

namespace skewer\build\Page\CatalogViewer\State;

use skewer\build\Catalog\Collections\Api;
use skewer\components\catalog;

class CollectionOnMain extends Prototype
{
    protected $sTpl = 'list';

    protected $list = [];

    /** @var array Набор шаблонов для каталога */
    public static $aTemplates = [
        'list' => [
            'title' => 'Editor.type_collection_list',
            'file' => 'CollectionOnMain.list.twig',
        ],

        'slider' => [
            'title' => 'Editor.type_collection_slider',
            'file' => 'CollectionOnMain.slider.twig',
        ],
    ];

    public function init()
    {
        if ($iCollectionCard = Api::getCollectionBySection($this->oModule->onMainCollectionSection)) {
            $this->oModule->setData('section', $this->oModule->onMainCollectionSection);

            $this->list = catalog\ObjectSelector::getCollections($iCollectionCard)
                ->condition('active', 1)
                ->condition('on_main', 1)
                ->withSeo($this->getSection())
                ->parse();

            \Yii::$app->router->setLastModifiedDate(Api::getMaxLastModifyDate($iCollectionCard));
        }
    }

    public function build()
    {
        // парсинг
        $this->oModule->setData('aObjectList', $this->list);
        $this->oModule->setData('titleOnMain', $this->getModuleField('titleOnMain'));
        $this->oModule->setData('moduleGroup', $this->getModuleGroup());
        $this->oModule->setData('gallerySettings', htmlentities(\skewer\components\GalleryOnPage\Api::getSettingsByEntity('MainCollection', true), ENT_QUOTES, 'UTF-8'));

        // шаблон
        $this->sTpl = $this->getModuleField('template');

        if (!isset(self::$aTemplates[$this->sTpl])) {
            $this->oModule->setTemplate($this->sTpl);
        } else {
            $this->oModule->setTemplate(self::$aTemplates[$this->sTpl]['file']);
        }
    }
}
