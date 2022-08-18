<?php

namespace skewer\build\Catalog\Goods;

use skewer\base\section\Tree;
use skewer\components\catalog;
use skewer\components\seo\Frequency;
use skewer\components\seo\SeoPrototype;
use skewer\components\seo\Template;
use yii\helpers\ArrayHelper;

class SeoGood extends SeoPrototype
{
    /**
     * {@inheritdoc}
     */
    public static function getTitleEntity()
    {
        return 'Товар';
    }

    public static function getGroup()
    {
        return 'good';
    }

    /**
     * {@inheritdoc}
     */
    public static function getAlias()
    {
        return 'catalogDetail';
    }

    /**
     * {@inheritdoc}
     */
    public function extractReplaceLabels($aParams)
    {
        $aOut = [];

        $aDataEntity = $this->getDataEntity();

        $sTitle = ArrayHelper::getValue($aDataEntity, 'fields.title.value', '');

        $aOut['label_catalog_title_upper'] = $sTitle;
        $aOut['label_catalog_title_lower'] = $this->toLower($sTitle);

        // добавление всех полей
        if (!empty($aDataEntity['fields'])) {
            foreach ($aDataEntity['fields'] as $sFieldName => $aField) {
                if ($aField['type'] == 'money') {
                    if ($aField['value']) {
                        $aField['value'] = round($aField['value'], 2, PHP_ROUND_HALF_UP);
                        $aOut[$aField['title']] = $aField['value'];
                        if (isset($aField['measure'])) {
                            $aOut[$aField['title']] .= ' ' . $aField['measure'];
                        }
                    } else {
                        $aOut[$aField['title']] = '';
                    }
                } elseif (isset($aField['title'], $aField['html'])) {
                    $aOut[$aField['title']] = strip_tags($aField['html']);
                }
            }
        }

        if (isset($aParams['label_number_photo'])) {
            $aOut['label_number_photo'] = $aParams['label_number_photo'];
        }

        return $aOut;
    }

    public function getPriority()
    {
        return 0.9;
    }

    public function calculateFrequency()
    {
        return Frequency::DAILY;
    }

    public function loadDataEntity()
    {
        if ($this->iEntityId) {
            $GoodData = catalog\GoodsSelector::get($this->iEntityId);
        }

        if ($GoodData) {
            $this->aDataEntity = $GoodData ? $GoodData : [];
            if (isset($this->aDataEntity['card'])) {
                self::setExtraAlias($this->aDataEntity['card']);
            }
        }

        return $this;
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
        Tree::getSectionByPath($sPath, $sTail);
        $sTail = trim($sTail, '/');

        return ($aRecord = catalog\GoodsSelector::get($sTail))
            ? $aRecord['id']
            : false;
    }

    /**
     * Получить пустой товар
     *
     * @param int|string $sCard - карточка товара
     *
     * @return array
     */
    public static function getBlankGood($sCard)
    {
        $oGoodsRow = catalog\GoodsRow::create($sCard);

        $aGoodData = catalog\Parser::get($oGoodsRow->getFields())
            ->parseGood([], [], false);

        return $aGoodData;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndividualTemplate4Section()
    {
        return Template::getByAliases(static::getAlias(), $this->iSectionId);
    }
}
