<?php

namespace skewer\build\Adm\Gallery;

use skewer\base\section\Tree;
use skewer\components\gallery\Album;
use skewer\components\seo\SeoPrototype;

class Seo extends SeoPrototype
{
    /**
     * {@inheritdoc}
     */
    public static function getTitleEntity()
    {
        return 'Альбом';
    }

    public static function getGroup()
    {
        return 'gallery';
    }

    /**
     * {@inheritdoc}
     */
    public static function getAlias()
    {
        return 'galleryDetail';
    }

    /**
     * {@inheritdoc}
     */
    public function extractReplaceLabels($aParams)
    {
        $aDataEntity = $this->getDataEntity();

        $aData = [
            'label_gallery_title_upper' => $aDataEntity['title'],
            'label_gallery_title_lower' => mb_strtolower($aDataEntity['title']),
        ];

        if (isset($aParams['label_number_photo'])) {
            $aData['label_number_photo'] = $aParams['label_number_photo'];
        }

        return $aData;
    }

    public function loadDataEntity()
    {
        if ($oAlbum = Album::getById($this->iEntityId)) {
            $this->aDataEntity = $oAlbum->getAttributes();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getSearchClassName()
    {
        return Search::className();
    }

    /**
     * {@inheritdoc}
     */
    public function doExistRecord($sPath)
    {
        $sTail = '';
        $iSectionId = Tree::getSectionByPath($sPath, $sTail);
        $sTail = trim($sTail, '/');

        return ($aRecord = Album::getByAlias($sTail, $iSectionId))
            ? $aRecord['id']
            : false;
    }

    /**
     * {@inheritdoc}
     */
    public function editableSeoTemplateFields()
    {
        return [
            'title',
            'description',
            'keywords',
            'altTitle',
        ];
    }
}
