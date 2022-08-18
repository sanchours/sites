<?php

namespace skewer\build\Adm\FAQ;

use skewer\base\section\Tree;
use skewer\build\Page\FAQ;
use skewer\components\seo\SeoPrototype;
use yii\helpers\ArrayHelper;

class Seo extends SeoPrototype
{
    /**
     * {@inheritdoc}
     */
    public static function getTitleEntity()
    {
        return 'Вопрос-ответ';
    }

    public static function getGroup()
    {
        return 'faq';
    }

    /**
     * {@inheritdoc}
     */
    public static function getAlias()
    {
        return 'faqDetail';
    }

    /**
     * {@inheritdoc}
     */
    public function extractReplaceLabels($aParams)
    {
        return [
            'label_faq_title_upper' => strip_tags(ArrayHelper::getValue($this->aDataEntity, 'content', '')),
            'label_faq_title_lower' => mb_strtolower(strip_tags(ArrayHelper::getValue($this->aDataEntity, 'content', ''))),
        ];
    }

    public function loadDataEntity()
    {
        $this->aDataEntity = FAQ\Api::getFAQById($this->iEntityId);
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

        return ($oRecord = models\Faq::getByAlias($sTail, $iSectionId))
            ? $oRecord->id
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
        ];
    }
}
