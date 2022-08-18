<?php

namespace skewer\build\Adm\News;

use skewer\build\Tool\SeoGen\importer\Api as ImporterApi;
use skewer\build\Tool\SeoGen\importer\Prototype;

class Importer extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'news';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Новости';
    }

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
    public static function getSections4Import()
    {
        return ImporterApi::getSections4ImportByModuleName('News');
    }
}
