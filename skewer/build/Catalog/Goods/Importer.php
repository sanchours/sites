<?php

namespace skewer\build\Catalog\Goods;

use skewer\build\Tool\SeoGen\importer\Api as ImporterApi;
use skewer\build\Tool\SeoGen\importer\Prototype;

class Importer extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'goods';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Каталог';
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableSeoEntity()
    {
        return [
            SeoGood::className(),
            SeoGoodModifications::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSections4Import()
    {
        return ImporterApi::getSections4ImportByModuleName('CatalogViewer');
    }
}
