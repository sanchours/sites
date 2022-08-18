<?php
/**
 * Created by PhpStorm.
 * User: ram
 * Date: 16.05.2018
 * Time: 12:07.
 */

namespace skewer\build\Adm\Catalog\view;

use skewer\components\ext\view\FormView;

class Settings extends FormView
{
    /** @var array */
    public $fieldData;

    /** @var array */
    public $data;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSelect('template', \Yii::t('catalog', 'listTpl'), $this->fieldData['listPageTemplates'])
            ->fieldInt('onPage', \Yii::t('catalog', 'listCnt'), ['minValue' => 0])
            ->field('showFilter', \Yii::t('catalog', 'showListFilters'), 'check')
            ->field('showSort', \Yii::t('catalog', 'showListSort'), 'check')
            ->fieldString('buyFormSection', \Yii::t('catalog', 'formSection'))
            ->fieldSelect('relatedTpl', \Yii::t('catalog', 'relatedTpl'), $this->fieldData['listPageTemplates'])
            ->fieldSelect('includedTpl', \Yii::t('catalog', 'includedTpl'), $this->fieldData['listPageTemplates'])
            ->fieldCheck('showSubSectionObjects', \Yii::t('catalog', 'show_sub_section'));

        if ($this->fieldData['showRecentlyViewed']) {
            $this->_form
                ->fieldSelect('recentlyViewedTpl', \Yii::t('catalog', 'recentlyViewedTpl'), $this->fieldData['listPageTemplates'])
                ->fieldInt('recentlyViewedOnPage', \Yii::t('catalog', 'recentlyViewedOnPage'), ['minValue' => 0, 'maxValue' => $this->fieldData['maxCountGoodOnPage']]);
        }

        if ($this->fieldData['randomRelated']) {
            $this->_form->fieldMultiSelect('related_from', \Yii::t('catalog', 'related_from'), $this->fieldData['listPageRelatedList']);
        }

        $this->_form
            ->setValue($this->data)
            ->buttonSave('SaveConfig')
            ->buttonCancel('list');
    }
}
