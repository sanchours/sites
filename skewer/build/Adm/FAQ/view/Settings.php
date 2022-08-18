<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 18.11.2016
 * Time: 13:49.
 */

namespace skewer\build\Adm\FAQ\view;

use skewer\components\ext\view\FormView;

class Settings extends FormView
{
    public $items = [];

    public $languages;

    public $languageFilter;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->buttonSave('saveSettings')
            ->buttonCancel();

        if (count($this->languages) > 1) {
            $this->_form->filterSelect('filter_language', $this->languages, $this->languageFilter, \Yii::t('adm', 'language'), ['set' => true]);
            $this->_form->setFilterAction('settings');
        }

        $this->_form
            ->field('info', '', 'show', ['hideLabel' => true])
            ->fieldString('title_admin', \Yii::t('faq', 'mail_title'))
            ->field('content_admin', \Yii::t('faq', 'mail_body'), 'wyswyg', ['listColumns.flex' => 1, 'growMax' => 500, 'grow' => true])
            ->fieldString('title_user', \Yii::t('faq', 'mail_title_user'))
            ->field('content_user', \Yii::t('faq', 'mail_body_user'), 'wyswyg', ['listColumns.flex' => 1, 'growMax' => 500, 'grow' => true])
            ->field('onNotif', \Yii::t('faq', 'enable_notifications'), 'check')
            ->fieldString('notifTitleApprove', \Yii::t('faq', 'email_approve_subject'))
            ->field('notifContentApprove', \Yii::t('faq', 'email_approve_text'), 'wyswyg', ['listColumns.flex' => 1, 'growMax' => 500, 'grow' => true])
            ->fieldString('notifTitleReject', \Yii::t('faq', 'email_reject_subject'))
            ->field('notifContentReject', \Yii::t('faq', 'email_reject_text'), 'wyswyg', ['listColumns.flex' => 1, 'growMax' => 500, 'grow' => true]);

        $this->_form->setValue($this->items);
    }
}
