<?php

namespace skewer\build\Adm\Gallery;

use skewer\build\Tool\SeoGen\importer\Api as ImporterApi;
use skewer\build\Tool\SeoGen\importer\Prototype;

class Importer extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function getAvailableSeoEntity()
    {
        return [
            Seo::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'gallery';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Фотоальбомы';
    }

    /**
     * {@inheritdoc}
     */
    public static function getSections4Import()
    {
        return ImporterApi::getSections4ImportByModuleName('Gallery');
    }
}
