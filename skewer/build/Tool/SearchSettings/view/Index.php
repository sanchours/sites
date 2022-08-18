<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 26.01.2017
 * Time: 18:17.
 */

namespace skewer\build\Tool\SearchSettings\view;

use skewer\components\ext\view\FormView;

class Index extends FormView
{
    public $bHasCatalogModule;
    public $aTypeList;
    public $aSearchTypeList;
    public $aTemplates;
    public $aValue;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        if ($this->bHasCatalogModule) {
            $this->_form->fieldSelect('search_type', \Yii::t('search', 'search_type'), $this->aTypeList, [], false);
        }

        $this->_form->fieldSelect('type', \Yii::t('search', 'title_default_type'), $this->aSearchTypeList, [], false);

        if ($this->bHasCatalogModule) {
            $this->_form->fieldSelect('tpl_name', \Yii::t('search', 'typeTpl'), $this->aTemplates, [], false);
        }

        $this->_form
            ->fieldCheck('hidePlaceHolder', \Yii::t('search', 'hidePlaceHolder'))
            ->fieldInt('countSearch', \Yii::t('search', 'countSearch'), ['minValue' => 0])
            ->setValue($this->aValue)
            ->buttonSave('save')
            ->buttonCancel();
    }
}
