<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 18.01.2017
 * Time: 18:24.
 */

namespace skewer\build\Tool\Languages\view;

use skewer\components\ext\view\FormView;

class DefaultLanguage extends FormView
{
    public $bOneActiveLanguage;
    public $aActiveLanguages;
    public $aAdmLanguages;
    public $sLanguage;
    public $sAdminLanguage;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        if ($this->bOneActiveLanguage) {
            $this->_form->fieldSelect('language', \Yii::t('languages', 'select_language'), $this->aActiveLanguages, [], false);
        }
        $this->_form->fieldSelect('admin_language', \Yii::t('languages', 'select_admin_language'), $this->aAdmLanguages, [], false)
            ->setValue([
                'language' => $this->sLanguage,
                'admin_language' => $this->sAdminLanguage,
            ])
            ->buttonSave('preSaveDefault')
            ->buttonCancel();
    }
}
