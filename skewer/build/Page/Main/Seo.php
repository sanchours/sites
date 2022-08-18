<?php

namespace skewer\build\Page\Main;

use skewer\base\section\Tree;
use skewer\build\Adm\Tree\Search;
use skewer\build\Page\Text;
use skewer\components\seo\Frequency;
use skewer\components\seo\SeoPrototype;
use skewer\helpers\Html;
use yii\helpers\ArrayHelper;

class Seo extends SeoPrototype
{
    /**
     * {@inheritdoc}
     */
    public static function getTitleEntity()
    {
        return 'Раздел';
    }

    public static function getGroup()
    {
        return 'section';
    }

    /**
     * {@inheritdoc}
     */
    public static function getAlias()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function extractReplaceLabels($aParams)
    {
        return [];
    }

    public function loadDataEntity()
    {
        $aData = Tree::getCachedSection($this->iSectionId);
        $aData['text'] = Text\Api::getTextContentFromZone($this->iSectionId);
        $this->aDataEntity = $aData;
    }

    /**
     * Пересчет значения приоритета(priority).
     *
     * @return float|int
     */
    public function getPriority()
    {
        $fPriority = $fTemplatePriority = parent::getPriority();

        $oSection = Tree::getSection($this->iSectionId);

        if ($oSection->parent == \Yii::$app->sections->templates()) {
            return $fTemplatePriority;
        }

        $sText = ArrayHelper::getValue($this->aDataEntity, 'text', '');

        if (!Html::hasContent($sText)) {
            $fPriority -= 0.2;
        }

        return $fPriority;
    }

    /**
     * Пересчет значения частоты(frequency).
     *
     * @return string
     */
    public function calculateFrequency()
    {
        $sFrequency = $sTemplateFrequency = parent::calculateFrequency();

        $oSection = Tree::getSection($this->iSectionId);

        if ($oSection->parent == \Yii::$app->sections->templates()) {
            return $sTemplateFrequency;
        }

        $sText = ArrayHelper::getValue($this->aDataEntity, 'text', '');

        if (!Html::hasContent($sText)) {
            return Frequency::MONTHLY;
        }

        return $sFrequency;
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

        return ($iSectionId && !$sTail)
            ? $iSectionId
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
