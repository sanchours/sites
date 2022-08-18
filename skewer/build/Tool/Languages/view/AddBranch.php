<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 18.01.2017
 * Time: 17:51.
 */

namespace skewer\build\Tool\Languages\view;

use skewer\components\ext\view\FormView;

class AddBranch extends FormView
{
    public $aLanguages;
    public $aCopy;
    public $aParams;
    public $sCurrent;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_form
            ->fieldSelect('lang', \Yii::t('languages', 'field_lang'), $this->aLanguages, [], false)
            ->fieldSelect('source', \Yii::t('languages', 'field_source'), $this->aCopy, [], false)
            ->fieldCheck('copy', \Yii::t('languages', 'field_copy'))
            ->setValue($this->aParams)
            ->buttonSave('saveBranch')
            ->buttonCancel($this->sCurrent ? 'editLang' : 'init');
    }
}
