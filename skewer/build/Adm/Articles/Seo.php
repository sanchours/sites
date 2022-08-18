<?php

namespace skewer\build\Adm\Articles;

use skewer\base\section\Tree;
use skewer\build\Page\Articles\Model;
use skewer\components\seo\SeoPrototype;
use yii\helpers\ArrayHelper;

class Seo extends SeoPrototype
{
    /**
     * {@inheritdoc}
     */
    public static function getTitleEntity()
    {
        return 'Статья';
    }

    public static function getGroup()
    {
        return 'articles';
    }

    /**
     * {@inheritdoc}
     */
    public static function getAlias()
    {
        return 'articlesDetail';
    }

    /**
     * {@inheritdoc}
     */
    public function extractReplaceLabels($aParams)
    {
        $aOut = [
            'label_article_title_upper' => ArrayHelper::getValue($this->aDataEntity, 'title', ''),
            'label_article_title_lower' => $this->toLower(ArrayHelper::getValue($this->aDataEntity, 'title', '')),
        ];

        if (isset($aParams['label_number_photo'])) {
            $aOut['label_number_photo'] = $aParams['label_number_photo'];
        }

        return $aOut;
    }

    public function loadDataEntity()
    {
        if ($oRow = Model\Articles::getPublicById($this->iEntityId)) {
            $this->aDataEntity = $oRow->getData();
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
        $sAlias = '';
        $idSection = Tree::getSectionByPath($sPath, $sAlias);
        $sAlias = trim($sAlias, '/');

        /* @var Model\ArticlesRow $oRecord */
        return ($oRecord = Model\Articles::getPublicByAliasAndSec($sAlias, $idSection))
            ? $oRecord->id
            : false;
    }
}
