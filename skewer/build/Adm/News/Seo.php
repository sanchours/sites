<?php

namespace skewer\build\Adm\News;

use skewer\base\section\Tree;
use skewer\build\Adm\News\models\News;
use skewer\components\seo\SeoPrototype;
use yii\helpers\ArrayHelper;

class Seo extends SeoPrototype
{
    /**
     * {@inheritdoc}
     */
    public static function getTitleEntity()
    {
        return 'Новость';
    }

    public static function getGroup()
    {
        return 'news';
    }

    /**
     * {@inheritdoc}
     */
    public static function getAlias()
    {
        return 'newsDetail';
    }

    /**
     * {@inheritdoc}
     */
    public function extractReplaceLabels($aParams)
    {
        $aOut = [
            'label_news_title_upper' => ArrayHelper::getValue($this->aDataEntity, 'title', ''),
            'label_news_title_lower' => $this->toLower(ArrayHelper::getValue($this->aDataEntity, 'title', '')),
        ];

        if (isset($aParams['label_number_photo'])) {
            $aOut['label_number_photo'] = $aParams['label_number_photo'];
        }

        return $aOut;
    }

    public function loadDataEntity()
    {
        if ($oNews = News::findOne($this->iEntityId)) {
            $this->aDataEntity = $oNews->getAttributes();
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
        $idSection = Tree::getSectionByPath($sPath, $sTail);
        $sTail = trim($sTail, '/');

        return ($oRecord = News::getPublicNewsByAliasAndSec($sTail, $idSection))
            ? $oRecord->id
            : false;
    }
}
