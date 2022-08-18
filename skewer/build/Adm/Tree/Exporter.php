<?php

namespace skewer\build\Adm\Tree;

use skewer\base\section\Parameters;
use skewer\base\section\Template;
use skewer\base\section\Tree;
use skewer\build\Page\Main\Seo;
use skewer\build\Tool\SeoGen\Api;
use skewer\build\Tool\SeoGen\exporter\Prototype;
use skewer\components\excelHelpers;
use yii\helpers\ArrayHelper;

class Exporter extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sections';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Разделы';
    }

    /**
     * Выгружаемые поля.
     *
     * @return array
     */
    public function fields4Export()
    {
        $aFields = [
            'type' => [
                'value' => 'Type',
                'width' => 8,
                'style' => excelHelpers\Styles::$HEADER,
            ],
            'id' => [
                'value' => 'Id',
                'width' => 8,
                'style' => excelHelpers\Styles::$HEADER,
            ],
            'parent' => [
                'value' => 'Parent',
                'width' => 8,
                'style' => excelHelpers\Styles::$HEADER,
            ],
            'template' => [
                'value' => 'Шаблон',
                'width' => 15,
                'style' => excelHelpers\Styles::$HEADER,
            ],
            'visible' => [
                'value' => 'Видимость',
                'width' => 20,
                'style' => excelHelpers\Styles::$HEADER,
            ],
            'title' => [
                'value' => 'Название',
                'width' => 25,
                'style' => excelHelpers\Styles::$HEADER,
            ],
            'alias' => [
                'value' => 'Alias',
                'width' => 25,
                'style' => excelHelpers\Styles::$HEADER,
            ],
            'url' => [
                'value' => 'URL',
                'width' => 50,
                'style' => excelHelpers\Styles::$HEADER,
            ],
            'h1' => [
                'value' => 'Альтернативный заголовок H1',
                'width' => 45,
                'style' => excelHelpers\Styles::$HEADER,
            ],
            'seo_title' => [
                'value' => 'TITLE',
                'width' => 65,
                'style' => excelHelpers\Styles::$HEADER,
            ],
            'seo_description' => [
                'value' => 'DESCRIPTION',
                'width' => 65,
                'style' => excelHelpers\Styles::$HEADER,
            ],
            'seo_keywords' => [
                'value' => 'KEYWORDS',
                'width' => 65,
                'style' => excelHelpers\Styles::$HEADER,
            ],
            'staticContent' => [
                'value' => 'staticContent',
                'width' => 65,
                'style' => excelHelpers\Styles::$HEADER,
            ],
            'staticContent2' => [
                'value' => 'staticContent2',
                'width' => 65,
                'style' => excelHelpers\Styles::$HEADER,
            ],
        ];

        return $aFields;
    }

    public function getChunkData($iCurrentSectionId = 0, $iRowIndexEntity = 0)
    {
        if (!($aData = $this->getRecordWithinEntityByPosition($iCurrentSectionId, $iRowIndexEntity))) {
            return false;
        }

        $aRow = self::getBlankExportStructure();

        $aRow = array_merge($aRow, [
            'id' => ArrayHelper::getValue($aData, 'id', ''),
            'parent' => ArrayHelper::getValue($aData, 'parent', ''),
            'template' => Api::getTitleTemplateById(Parameters::getValByName($iCurrentSectionId, '.', 'template', true)),
            'visible' => Api::getTitleTypeVisibleById(ArrayHelper::getValue($aData, 'visible', '')),
            'title' => ArrayHelper::getValue($aData, 'title', ''),
            'type' => ArrayHelper::getValue($aData, 'type', ''),
            'alias' => ArrayHelper::getValue($aData, 'alias', ''),
            'url' => Tree::getSectionAliasPath($iCurrentSectionId),
            'h1' => Parameters::getValByName($iCurrentSectionId, 'title', 'altTitle') ?: '',
            'seo_title' => [
                'value' => ArrayHelper::getValue($aData, 'seo.title.value', ''),
                'style' => (!ArrayHelper::getValue($aData, 'seo.title.overriden', true)) ? excelHelpers\Styles::$GREEN : false,
            ],
            'seo_description' => [
                'value' => ArrayHelper::getValue($aData, 'seo.description.value', ''),
                'style' => (!ArrayHelper::getValue($aData, 'seo.description.overriden', true)) ? excelHelpers\Styles::$GREEN : false,
            ],
            'seo_keywords' => [
                'value' => ArrayHelper::getValue($aData, 'seo.keywords.value', ''),
                'style' => (!ArrayHelper::getValue($aData, 'seo.keywords.overriden', true)) ? excelHelpers\Styles::$GREEN : false,
            ],
            'staticContent' => ArrayHelper::getValue($aData, 'staticContent', ''),
            'staticContent2' => ArrayHelper::getValue($aData, 'staticContent2', ''),
        ]);

        return $aRow;
    }

    /**
     * Метод вернет данные раздела $iSectionId + seo данные.
     *
     * @param int $iSectionId
     * @param bool $iPosition - в методе данного класса не используется
     *
     * @return array
     */
    public function getRecordWithinEntityByPosition($iSectionId, $iPosition)
    {
        $oSeo = new Seo();
        $oSeo->setSectionId($iSectionId);
        $oSeo->loadDataEntity();
        $aSeoData = $oSeo->parseSeoData(['sectionId' => $iSectionId]);

        $aRow = [];
        $aRow['type'] = $oSeo::getTitleEntity();
        $aRow['url'] = Tree::getSectionAliasPath($iSectionId);
        $aRow['h1'] = Parameters::getValByName($iSectionId, 'title', 'altTitle') ?: '';
        $aRow['staticContent'] = Parameters::getShowValByName($iSectionId, 'staticContent', 'source') ?: '';
        $aRow['staticContent2'] = Parameters::getShowValByName($iSectionId, 'staticContent2', 'source') ?: '';
        $aRow['seo'] = $aSeoData;

        $aRow = array_merge($oSeo->getDataEntity(), $aRow);

        return $aRow;
    }

    /**
     * {@inheritdoc}
     */
    public function checkTemplateSection($iSectionId)
    {
        $iTpl = Parameters::getTpl($iSectionId);
        $aTemplateIds = ArrayHelper::getColumn(Template::getTemplateList(), 'id');

        $bRes = $iTpl && in_array($iTpl, $aTemplateIds);

        return $bRes;
    }
}
