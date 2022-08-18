<?php
/**
 * Created by PhpStorm.
 * User: ram
 * Date: 16.05.2018
 * Time: 9:59.
 */

namespace skewer\build\Adm\Catalog\view;

use skewer\components\ext\view\FormView;

class PageTypeForm extends FormView
{
    /** @var array */
    public $pageTypes;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSelect('section_type', \Yii::t('catalog', 'section_type'), $this->pageTypes, [], false)
            ->setValue([])
            ->buttonSave('SetPageType');
    }
}
