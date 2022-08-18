<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 18.01.2017
 * Time: 14:08.
 */

namespace skewer\build\Tool\Languages\view;

use skewer\components\ext\view\ListView;

class ShowKeys extends ListView
{
    public $sSearchFilter;
    public $aCategoryList;
    public $sCategoryFilter;
    public $aDataFilter;
    public $iDataFilter;
    public $bHasSrcLang;
    public $aStatusList;
    public $iStatusFilter;
    public $iStatusFilterAll;
    public $aValueList;
    public $aLanguages;
    public $bIsSystemLanguages;
    public $bShowAutoTranslate = false;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->filterText('search', $this->sSearchFilter)
            ->filterSelect('filter_category', $this->aCategoryList, $this->sCategoryFilter, \Yii::t('languages', 'field_category'))
            ->filterSelect('filter_data', $this->aDataFilter, $this->iDataFilter, \Yii::t('languages', 'filter_data'))
            //->fieldHide('data', \Yii::t('languages', 'filter_data'))
            ->fieldString('categoryData', \Yii::t('languages', 'field_category'), ['listColumns.flex' => 10])
            ->fieldString('message', \Yii::t('languages', 'field_message'), ['listColumns.flex' => 10])
            ->fieldString('value', \Yii::t('languages', 'field_value'), ['listColumns.flex' => 10])
            ->setFilterAction('showKeys');
        if ($this->bHasSrcLang) {
            $this->_list
                ->filterSelect(
                    'filter_status',
                    $this->aStatusList,
                    $this->iStatusFilter,
                    \Yii::t('languages', 'field_status'),
                    ['default' => $this->iStatusFilterAll]
                )
                ->fieldString('src', \Yii::t('languages', 'field_src'), ['listColumns.flex' => 8])
                ->fieldString('status_text', \Yii::t('languages', 'field_status'))
                ->buttonRowCustomJs('StatusGroupBtn');

            if ($this->bShowAutoTranslate) {
                $this->_list->buttonRowCustomJs('TranslateBtn');
            }
        }
        $this->_list
            ->setValue($this->aValueList, $this->onPage, $this->page, $this->total)
            ->setEditableFields(['value'], 'saveKey')
            ->buttonAddNew('addKey', \Yii::t('adm', 'add'), ['language' => $this->aLanguages])
            ->buttonCancel();

        if ($this->bIsSystemLanguages) {
            $this->_list->buttonRowCustomJs('OverrideBtn');
        }

        $this->_list->buttonRowCustomJs('DelBtn');
    }
}
