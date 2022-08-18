<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 18.01.2017
 * Time: 15:10.
 */

namespace skewer\build\Tool\Languages\view;

use skewer\components\ext\view\FormView;

class EditLangForm extends FormView
{
    public $iLangId;
    public $aLanguages;
    public $aData;
    public $sSave;
    public $bNotActiveLanguage;
    public $notIsNewRecord;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        if ($this->iLangId) {
            $this->_form->fieldHide('name', \Yii::t('languages', 'field_lang_name'))
                ->field('name_show', \Yii::t('languages', 'field_lang_name'), 'show');
        } else {
            $this->_form->fieldString('name', \Yii::t('languages', 'field_lang_name'));
        }
        $this->_form->fieldString('title', \Yii::t('languages', 'field_lang_title'))
            ->field('icon', \Yii::t('languages', 'field_lang_icon'), 'imagefile', [
                'subtext' => \Yii::t('Languages', 'field_lang_icon_description'),
            ]);

        if (!$this->iLangId) {
            $this->_form->fieldSelect('src_lang', \Yii::t('languages', 'field_lang_src_lang'), $this->aLanguages, [], false);
        } else {
            $this->_form->fieldString('src_lang', \Yii::t('languages', 'field_lang_src_lang'), ['disabled' => true]);
        }

        $this->_form
            ->field('active', \Yii::t('languages', 'field_lang_active'), 'check', ['disabled' => true])
            ->field('admin', \Yii::t('languages', 'field_lang_admin'), 'check')
            ->setValue($this->aData)
            ->buttonSave($this->sSave);

        if ($this->bNotActiveLanguage) {
            $this->_form->buttonAddNew('addBranch', \Yii::t('languages', 'addBranch'));
        } elseif ($this->notIsNewRecord) {
            $this->_form->buttonConfirm('deleteBranch', \Yii::t('languages', 'deleteBranch'), \Yii::t('languages', 'deleteBranchConfirm'), 'icon-delete');
        }
        $this->_form->buttonCancel();
    }
}
