<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 18.01.2017
 * Time: 13:26.
 */

namespace skewer\build\Tool\Languages\view;

use skewer\components\ext\view\ListView;

class LanguagesList extends ListView
{
    public $aLanguages;
    public $iCountNotActiveLanguage;
    public $bIsSystemMode;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->fieldString('name', \Yii::t('languages', 'field_lang_name'))
            ->fieldString('title', \Yii::t('languages', 'field_lang_title'), ['listColumns.flex' => 8])
            ->field('active', \Yii::t('languages', 'field_lang_active'), 'check')
            ->field('admin', \Yii::t('languages', 'field_lang_admin'), 'check', ['listColumns.flex' => 3])
            ->setValue($this->aLanguages)
            ->buttonAddNew('newLang', \Yii::t('languages', 'addLanguage'));

        if ($this->iCountNotActiveLanguage) {
            $this->_list->buttonAddNew('addBranch', \Yii::t('languages', 'addBranch'));
        }
        $this->_list->buttonRow('EditLang', \Yii::t('languages', 'edit'), 'icon-edit')
            ->buttonRow('showKeys', \Yii::t('languages', 'edit_words'), 'icon-page', 'edit_form');

        if ($this->bIsSystemMode) {
            $this->_list->buttonRowDelete('delLang');
        }
        $this->_list->buttonEdit('defaultLanguage', \Yii::t('languages', 'defaultLanguage'));
    }
}
