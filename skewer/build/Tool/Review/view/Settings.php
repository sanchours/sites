<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 23.01.2017
 * Time: 10:51.
 */

namespace skewer\build\Tool\Review\view;

use skewer\components\ext\view\FormView;

class Settings extends FormView
{
    public $bNotSectionId;
    public $aLanguages;
    public $sLanguageFilter;
    public $aItems;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        if ($this->bNotSectionId && count($this->aLanguages) > 1) {
            $this->_form
                ->filterSelect('filter_language', $this->aLanguages, $this->sLanguageFilter, \Yii::t('adm', 'language'), ['set' => true])
                ->setFilterAction('settings');
        }
        $this->_form
            ->field('info', '', 'show', ['hideLabel' => true])

            ->fieldString('mail.title', \Yii::t('review', 'settings_admin_letter_title'))
            ->field('mail.content', \Yii::t('review', 'settings_content'), 'wyswyg')
            ->fieldString('mail.notifTitleNew', \Yii::t('review', 'settings_new_subject'))
            ->field('mail.notifContentNew', \Yii::t('review', 'settings_new_content'), 'wyswyg')

            ->field('mail.onNotif', \Yii::t('review', 'settings_enable_notifications'), 'check')
            ->fieldString('mail.notifTitleApprove', \Yii::t('review', 'settings_approve_subject'))
            ->field('mail.notifContentApprove', \Yii::t('review', 'settings_approve_content'), 'wyswyg')
            ->fieldString('mail.notifTitleReject', \Yii::t('review', 'settings_reject_subject'))
            ->field('mail.notifContentReject', \Yii::t('review', 'settings_reject_content'), 'wyswyg')

            ->buttonSave('saveSettings')
            ->buttonCancel()
            ->setValue($this->aItems);
    }
}
