<?php
/**
 * Created by PhpStorm.
 * User: ram
 * Date: 16.05.2018
 * Time: 12:52.
 */

namespace skewer\build\Adm\Catalog\view;

use skewer\components\ext\view\FormView;

class CollectionSetting extends FormView
{
    /** @var array */
    public $listPageTemplates;

    /** @var array */
    public $data;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSelect('template', \Yii::t('catalog', 'listTpl'), $this->listPageTemplates)
            ->fieldInt('onPageCollection', \Yii::t('catalog', 'listColectionCnt'))
            ->fieldInt('onPage', \Yii::t('catalog', 'listCnt'))
            ->fieldCheck('showSort', \Yii::t('catalog', 'showListSort'))
            ->fieldCheck('showFilter', \Yii::t('catalog', 'show_filter_in_collection_page'))
            ->setValue($this->data)
            ->buttonSave('SaveConfig');
    }
}
