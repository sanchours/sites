<?php

namespace skewer\build\Tool\Auth\view;

use skewer\components\ext\view\FormView;

class Letters extends FormView
{
    /** @var [] */
    public $items;

    public $lang = '';

    /** @var array набор доступных яхыков */
    public $langList = [];

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        // создаем форму
        $oFormBuilder = $this->_form;

        $oFormBuilder->buttonSave('saveMail');
        $oFormBuilder->buttonCancel();

        if (count($this->langList) > 1) {
            $oFormBuilder->filterSelect('filter_language', $this->langList, $this->lang, \Yii::t('adm', 'language'), ['set' => true]);
            $oFormBuilder->setFilterAction('showMail');
        }

        $oFormBuilder
            ->field('info', '', 'show', ['hideLabel' => true])

            ->fieldString('mail_title_mail_activate', \Yii::t('auth', 'edit_title_mail_activate'))
            ->field('mail_activate', \Yii::t('auth', 'edit_mail_activate'), 'wyswyg')

            ->fieldString('mail_title_admin_newuser', \Yii::t('auth', 'edit_mail_title_admin_newuser'))
            ->field('mail_admin_activate', \Yii::t('auth', 'edit_main_admin_activate'), 'wyswyg')

            ->fieldString('mail_title_user_newuser', \Yii::t('auth', 'edit_mail_title_user_newuser'))
            ->field('mail_user_activate', \Yii::t('auth', 'edit_main_user_activate'), 'wyswyg')

            ->fieldString('mail_title_new_pass', \Yii::t('auth', 'edit_mail_title_new_pass'))
            ->field('mail_new_pass', \Yii::t('auth', 'edit_mail_new_pass'), 'wyswyg')

            ->fieldString('mail_title_reset_password', \Yii::t('auth', 'edit_mail_title_reset_password'))
            ->field('mail_reset_password', \Yii::t('auth', 'edit_mail_reset_password'), 'wyswyg')

            ->fieldString('mail_title_mail_banned', \Yii::t('auth', 'edit_title_mail_banned'))
            ->field('mail_banned', \Yii::t('auth', 'edit_mail_banned'), 'wyswyg')

            ->fieldString('mail_title_mail_close_banned', \Yii::t('auth', 'edit_title_mail_close_banned'))
            ->field('mail_close_ban', \Yii::t('auth', 'edit_mail_close_ban'), 'wyswyg');

        $oFormBuilder->setValue($this->items);
    }
}
