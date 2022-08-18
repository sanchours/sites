<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 27.01.2017
 * Time: 11:04.
 */

namespace skewer\build\Tool\ServiceSections\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $bManyLanguages;
    public $aLanguages;
    public $sLangFilter;
    public $aServiceSections;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->fieldShow('id', 'ID')
            ->fieldString('name', \Yii::t('languages', 'section_name'), ['listColumns' => ['flex' => 3]])
            ->fieldString('title', \Yii::t('languages', 'section_title'), ['listColumns' => ['flex' => 5]])
            ->buttonAddNew('addNewSection', \Yii::t('languages', 'add_system_section'))
            ->fieldInt('value', \Yii::t('languages', 'section_value'))
            ->fieldString('language', \Yii::t('languages', 'section_language'));

        if ($this->bManyLanguages) {
            $this->_list->filterSelect('lang_filter', $this->aLanguages, $this->sLangFilter, \Yii::t('languages', 'section_lang_filter'));
        }

        $this->_list
            ->setValue($this->aServiceSections)
            ->setEditableFields(['value'], 'save');
    }
}
