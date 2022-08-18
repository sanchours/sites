<?php

namespace skewer\build\Catalog\ViewSettings;

use skewer\base\section\models\ParamsAr;
use skewer\base\section\Parameters;
use skewer\base\section\Template;
use skewer\base\SysVar;
use skewer\build\Catalog\LeftList\ModulePrototype;
use skewer\build\Page\RecentlyViewed;
use yii\base\UserException;

class Module extends ModulePrototype
{
    public function actionInit()
    {
        $aData = [
            'aTplList' => self::getTplList(),
            'bGoodsRelated' => SysVar::get('catalog.goods_related'),
            'bGoodsInclude' => SysVar::get('catalog.goods_include'),
            'aCheckList' => self::getCheckList(),
            'bRecentlyViewed' => (bool) SysVar::get('catalog.goods_recentlyViewed'),
        ];

        $this->render(new view\Index($aData));
    }

    public function actionSave()
    {
        $keys = ['template', 'relatedTpl', 'includedTpl', 'onPage', 'showFilter', 'showSort', 'recentlyViewedTpl', 'recentlyViewedOnPage'];
        $data = $this->getInData();

        if (isset($data['recentlyViewedOnPage']) && ($data['recentlyViewedOnPage'] > RecentlyViewed\Module::getMaxCountGoodOnPage())) {
            throw new UserException(\Yii::t('catalog', 'error_exceeded_max_value', ['paramName' => \Yii::t('catalog', 'recentlyViewedOnPage'), 'maxValue' => RecentlyViewed\Module::getMaxCountGoodOnPage()]));
        }
        if (isset($data['sectionRecentlyViewedOnPage']) && ($data['sectionRecentlyViewedOnPage'] > RecentlyViewed\Module::getMaxCountGoodOnPage())) {
            throw new UserException(\Yii::t('catalog', 'error_exceeded_max_value', ['paramName' => \Yii::t('catalog', 'section_recentlyViewedOnPage'), 'maxValue' => RecentlyViewed\Module::getMaxCountGoodOnPage()]));
        }
        foreach ($keys as $key) {
            $val = $data[$key] ?? '';
            if ($val) {
                if ($val == -1) {
                    $val = 0;
                }
                if (in_array($key, ['onPage', 'template'])) {
                    $this->saveParamWithCard($key, $val);
                } else {
                    $this->saveParam($key, $val);
                }
            }
        }

        if (!empty($data['sectionRecentlyViewedTpl']) || !empty($data['sectionRecentlyViewedOnPage'])) {
            self::globalUpdateParamsRecentlyViewedModule($data['sectionRecentlyViewedTpl'], $data['sectionRecentlyViewedOnPage']);
        }

        $this->addMessage('', \Yii::t('catalog', 'good_save_msg'));
        \Yii::$app->router->updateModificationDateSite();

        $this->actionInit();
    }

    protected function saveParam($key, $val)
    {
        return Parameters::updateByName('content', $key, $val);
    }

    /**
     * @param $ParamName
     * @param $value
     *
     * @return int
     */
    protected function saveParamWithCard($ParamName, $value)
    {
        $aSectionIds = Parameters::getSectionIdListByParamName('content', 'defCard');
        $aSectionIds[] = Template::getCatalogTemplate();

        return Parameters::updateByNameInSections('content', $ParamName, $value, $aSectionIds);
    }

    public function getTplList()
    {
        $aList = ['list', 'gallery', 'table'];

        $aOut = [];
        foreach ($aList as $sName) {
            $aOut[$sName] = \Yii::t('catalog', 'tpl_' . $sName);
        }

        return $aOut;
    }

    protected function getCheckList()
    {
        $aList = ['list', 'gallery', 'table'];

        $aOut = [];
        foreach ($aList as $sName) {
            $aOut[$sName] = \Yii::t('catalog', 'tpl_' . $sName);
        }

        return [
            0 => \Yii::t('catalog', 'dontchange'),
            1 => \Yii::t('catalog', 'yes'),
            -1 => \Yii::t('catalog', 'no'),
        ];
    }

    /**
     * Глобальное обновление параметров модуля "Недавно просмотренные" в разделах.
     *
     * @param int $iRecentlyViewedOnPage
     * @param string $sRecentlyViewedTpl
     */
    private static function globalUpdateParamsRecentlyViewedModule($sRecentlyViewedTpl = '', $iRecentlyViewedOnPage = 0)
    {
        // обновить параметры `recentlyViewedTpl` и `recentlyViewedOnPage`
        // во всех группах параметров, где object=RecentlyViewed

        $aParameters = Parameters::getList()
            ->name(Parameters::object)
            ->value(RecentlyViewed\Module::getNameModule())
            ->asArray()
            ->get();

        $aWhereCondition = [];

        foreach ($aParameters as $item) {
            $aWhereCondition[] = [
                'group' => $item['group'],
                'parent' => $item['parent'],
            ];
        }

        if ($aWhereCondition) {
            array_unshift($aWhereCondition, 'or');

            if ($iRecentlyViewedOnPage) {
                \Yii::$app->db->createCommand()
                    ->update(ParamsAr::tableName(), ['value' => $iRecentlyViewedOnPage], ['and', ['name' => 'iOnPage'], $aWhereCondition])
                    ->execute();
            }

            if ($sRecentlyViewedTpl) {
                \Yii::$app->db->createCommand()
                    ->update(ParamsAr::tableName(), ['value' => $sRecentlyViewedTpl], ['and', ['name' => 'sTpl'], $aWhereCondition])
                    ->execute();
            }
        }
    }
}
