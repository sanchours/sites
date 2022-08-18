<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 23.01.2017
 * Time: 12:02.
 */

namespace skewer\build\Tool\Review\view;

use skewer\components\ext\view\FormView;

class SettingsCatalog extends FormView
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

            ->fieldString('mail.catalog.title', \Yii::t('review', 'settings_catalog_admin_letter_title'))
            ->field('mail.catalog.content', \Yii::t('review', 'settings_catalog_content'), 'wyswyg')
            ->fieldString('mail.catalog.notifTitleNew', \Yii::t('review', 'settings_catalog_new_subject'))
            ->field('mail.catalog.notifContentNew', \Yii::t('review', 'settings_catalog_new_content'), 'wyswyg')

            ->field('mail.catalog.onNotif', \Yii::t('review', 'settings_catalog_enable_notifications'), 'check')
            ->fieldString('mail.catalog.notifTitleApprove', \Yii::t('review', 'settings_catalog_approve_subject'))
            ->field('mail.catalog.notifContentApprove', \Yii::t('review', 'settings_catalog_approve_content'), 'wyswyg')
            ->fieldString('mail.catalog.notifTitleReject', \Yii::t('review', 'settings_catalog_reject_subject'))
            ->field('mail.catalog.notifContentReject', \Yii::t('review', 'settings_catalog_reject_content'), 'wyswyg')

            ->buttonSave('saveSettingsCatalog')
            ->buttonCancel()
            ->setValue($this->aItems);
    }
}
