<?php

namespace skewer\components\catalog;

use skewer\base\site\Layer;
use skewer\build\Page\CatalogMaps;

class Attr
{
    const SHOW_IN_LIST = 'show_in_list';
    const SHOW_IN_DETAIL = 'show_in_detail';
    const SHOW_IN_SORTPANEL = 'show_in_sortpanel';
    const ACTIVE = 'active';
    const MEASURE = 'measure';
    const SHOW_IN_PARAMS = 'show_in_params';
    const SHOW_IN_TAB = 'show_in_tab';
    const SHOW_IN_FILTER = 'show_in_filter';
    const IS_UNIQ = 'is_uniq';
    const SHOW_IN_TABLE = 'show_in_table';

    const SHOW_IN_CART = 'show_in_cart';
    const SHOW_TITLE = 'show_title';
    const SHOW_IN_MAP = 'show_in_map';
    const SHOW_TITLE_IN_MAP = 'show_title_in_map';
    const SHOW_IN_MODIFICATION = 'show_in_modification';
    const SHOW_IN_SEARCH = 'show_in_search';
    const NOT_EDIT_FIELD = 'not_edit_field';
    const SHOW_IN_QUICKVIEW = 'show_in_quickview';
    const SHOW_IN_PARAMS_QUICKVIEW = 'show_in_params_quickview';

    /**
     * Список атрибутов.
     *
     * @param bool $bWithSystemAttrs - вместе с системными?
     *
     * @return array
     */
    public static function getList($bWithSystemAttrs = true)
    {
        $aAttrs = [
            ['id' => self::SHOW_IN_LIST, 'name' => 'show_in_list', 'title' => \Yii::t('catalog', 'attr_show_in_list'), 'type' => 'check', 'default' => 1],
            ['id' => self::SHOW_IN_DETAIL, 'name' => 'show_in_detail', 'title' => \Yii::t('catalog', 'attr_show_in_detail'), 'type' => 'check', 'default' => 1],
            ['id' => self::SHOW_IN_SORTPANEL, 'name' => 'show_in_sortpanel', 'title' => \Yii::t('catalog', 'attr_show_in_sortpanel'), 'type' => 'check', 'default' => 0],
            ['id' => self::ACTIVE, 'name' => 'active', 'title' => \Yii::t('catalog', 'attr_active'), 'type' => 'check', 'default' => 1],
            ['id' => self::MEASURE, 'name' => 'measure', 'title' => \Yii::t('catalog', 'attr_measure'), 'type' => 'string', 'default' => ''],
            ['id' => self::SHOW_IN_PARAMS, 'name' => 'show_in_params', 'title' => \Yii::t('catalog', 'attr_show_in_params'), 'type' => 'check', 'default' => 0],
            ['id' => self::SHOW_IN_TAB, 'name' => 'show_in_tab', 'title' => \Yii::t('catalog', 'attr_show_in_tab'), 'type' => 'check', 'default' => 0],
            ['id' => self::SHOW_IN_FILTER, 'name' => 'show_in_filter', 'title' => \Yii::t('catalog', 'attr_show_in_filter'), 'type' => 'check', 'default' => 0],
            ['id' => self::IS_UNIQ, 'name' => 'is_uniq', 'title' => \Yii::t('catalog', 'attr_is_uniq'), 'type' => 'check', 'default' => 0],
            ['id' => self::SHOW_IN_TABLE, 'name' => 'show_in_table', 'title' => \Yii::t('catalog', 'attr_show_in_table'), 'type' => 'check', 'default' => 0],
//            [ 'id'=>13, 'name'=>'show_in_cart', 'title'=>\Yii::t( 'catalog', 'attr_show_in_cart'), 'type'=>'check', 'default'=>0 ],
            ['id' => self::SHOW_TITLE, 'name' => 'show_title', 'title' => \Yii::t('catalog', 'attr_show_title'), 'type' => 'check', 'default' => 1],
            ['id' => self::SHOW_IN_MODIFICATION, 'name' => 'show_in_modification', 'title' => \Yii::t('catalog', 'attr_show_in_modification'), 'type' => 'check', 'default' => 0],
        ];

        if (\skewer\build\Page\CatalogViewer\Api::checkQuickView()) {
            $aAttrs[] = ['id' => self::SHOW_IN_QUICKVIEW, 'name' => 'show_in_quickview', 'title' => \Yii::t('catalog', 'attr_show_in_quickview'), 'type' => 'check', 'default' => 0];
            $aAttrs[] = ['id' => self::SHOW_IN_PARAMS_QUICKVIEW, 'name' => 'show_in_params_quickview', 'title' => \Yii::t('catalog', 'attr_show_in_params_quickview'), 'type' => 'check', 'default' => 0];
        }

        if (\Yii::$app->register->moduleExists(CatalogMaps\Module::getNameModule(), Layer::PAGE)) {
            $aAttrsMap = [
                ['id' => self::SHOW_IN_MAP, 'name' => 'show_in_map', 'title' => \Yii::t('catalog', 'attr_show_in_map'), 'type' => 'check', 'default' => 0],
                ['id' => self::SHOW_TITLE_IN_MAP, 'name' => 'show_title_in_map', 'title' => \Yii::t('catalog', 'attr_show_title_in_map'), 'type' => 'check', 'default' => 0],
            ];
            $aAttrs = array_merge($aAttrs, $aAttrsMap);
        }

        // системные атрибуты
        if ($bWithSystemAttrs) {
            $aAttrs[] = ['id' => self::NOT_EDIT_FIELD, 'name' => 'not_edit_field', 'title' => \Yii::t('catalog', 'attr_not_edit_field'), 'type' => 'check', 'default' => 0];
            $aAttrs[] = ['id' => self::SHOW_IN_SEARCH, 'name' => 'show_in_search', 'title' => \Yii::t('catalog', 'attr_show_in_search'), 'type' => 'check', 'default' => 0];
        }

        return $aAttrs;
    }
}
