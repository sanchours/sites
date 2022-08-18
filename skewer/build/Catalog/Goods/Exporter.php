<?php

namespace skewer\build\Catalog\Goods;

use skewer\base\orm\Query;
use skewer\base\section\Parameters;
use skewer\base\section\Template;
use skewer\base\section\Tree;
use skewer\build\Tool\SeoGen\exporter\Prototype;
use skewer\components\catalog\Card;
use skewer\components\catalog\GoodsSelector;
use skewer\components\catalog\model\GoodsTable;
use skewer\components\catalog\model\SectionTable;
use skewer\components\catalog\Parser;
use skewer\components\catalog\Section;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

class Exporter extends Prototype
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

    public function initParams($aParams)
    {
        $sCatalogSections = ArrayHelper::getValue($aParams, 'catalog_sections');

        /** @var array Каталожные разделы */
        $aCatalogSections = StringHelper::explode($sCatalogSections, ',', true, true);

        if (array_search('all', $aCatalogSections) !== false) {
            $this->aSourceSections = array_keys(Section::getList(true));
        } else {
            $this->aSourceSections = $aCatalogSections;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRecordWithinEntityByPosition($iSectionId, $iPosition)
    {
        $aResult = Query::SelectFrom('co_' . Card::DEF_BASE_CARD)
            ->fields('id, parent')
            ->join('inner', GoodsTable::getTableName(), GoodsTable::getTableName(), 'co_' . Card::DEF_BASE_CARD . '.id=base_id')
            ->join('inner', SectionTable::getTableName(), '', GoodsTable::getTableName() . '.parent=goods_id')
            ->on('section_id', $iSectionId)
            ->limit(1, $iPosition)
            ->asArray()
            ->get();

        if (!isset($aResult[0])) {
            return false;
        }

        $aCurrentRecord = $aResult[0];

        $aGood = GoodsSelector::get($aCurrentRecord['id'], Card::DEF_BASE_CARD);

        if ($aGood['main_obj_id'] != $aGood['id']) {
            $oSeo = new SeoGoodModifications(0, $iSectionId, $aGood);
            $oSeo->setBaseGoodId($aCurrentRecord['parent']);
        } else {
            $oSeo = new SeoGood(0, $iSectionId, $aGood);
            $oSeo->setExtraAlias($aGood['card']);
        }

        $aSeoData = $oSeo->parseSeoData(['sectionId' => $iSectionId]);

        $aRow = array_merge($aGood, [
            'type' => $oSeo::getTitleEntity(),
            'url' => Parser::buildUrl($iSectionId, $aGood['id'], $aGood['alias']),
            'seo' => $aSeoData,
        ]);

        return $aRow;
    }

    /**
     * {@inheritdoc}
     */
    public function validateParams($aParams, &$aErrors = [])
    {
        $sCatalogSections = ArrayHelper::getValue($aParams, 'catalog_sections', '');

        if (!$sCatalogSections) {
            $aErrors[] = 'Не задано поле "раздел каталога"';

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function buildFieldInForm(\skewer\base\ui\builder\FormBuilder $oForm)
    {
        $aStructure = Section::getListWithStructure();
        //Пересечение видимых и каталожных разделов
        $aVisibleCatalogSections = array_intersect_key(Tree::getVisibleSections(), Section::getList(false));
        $aDisabledVariants = array_diff_key($aStructure, $aVisibleCatalogSections);

        foreach ($aStructure as $k => &$item) {
            if (isset($aDisabledVariants[$k])) {
                $item = '<i style="color: #b3b3b3">' . $item . '</i>';
            }
        }

        $oForm->fieldMultiSelect(
            'catalog_sections',
            'Раздел каталога',
            ['all' => 'Все каталожные разделы'] + $aStructure,
            [],
            ['disabledVariants' => array_keys($aDisabledVariants)]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function checkTemplateSection($iSectionId)
    {
        return in_array(Parameters::getTpl($iSectionId), Template::getCatalogTemplate(false));
    }
}
