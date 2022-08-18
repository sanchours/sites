<?php
/**
 * Created by PhpStorm.
 * User: ram
 * Date: 16.05.2018
 * Time: 12:37.
 */

namespace skewer\build\Adm\Catalog\view;

use skewer\components\ext\view\FormView;

class SearchSetting extends FormView
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
            ->fieldInt('onPage', \Yii::t('catalog', 'listCnt'))
            ->setValue($this->data)
            ->buttonSave('SaveConfig');
    }
}
