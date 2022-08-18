<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 27.01.2017
 * Time: 18:01.
 */

namespace skewer\build\Tool\Subscribe\view;

use skewer\components\ext\view\FormView;

class Settings extends FormView
{
    public $aLanguages;
    public $sLanguageFilter;
    public $aItems;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        if (count($this->aLanguages) > 1) {
            $this->_form->filterSelect('filter_language', $this->aLanguages, $this->sLanguageFilter, \Yii::t('adm', 'language'), ['set' => true]);
            $this->_form->setFilterAction('settings');
        }

        $this->_form
            ->field('info', '', 'show', ['hideLabel' => true])
            ->fieldString('mail.mailTitle', \Yii::t('subscribe', 'mailTitle'))
            ->field('mail.mailText', \Yii::t('subscribe', 'mailText'), 'wyswyg')
            ->fieldString('mail.resultTitle', \Yii::t('subscribe', 'resultTitle'))
            ->fieldString('mail.successTitle', \Yii::t('subscribe', 'successTitle'))
            ->field('mail.successText', \Yii::t('subscribe', 'successText'), 'wyswyg')
            ->fieldString('mail.errorTitle', \Yii::t('subscribe', 'errorTitle'))
            ->field('mail.errorText', \Yii::t('subscribe', 'errorText'), 'wyswyg')
            ->buttonSave('saveMessageSettings')
            ->buttonCancel()
            ->setValue($this->aItems);
    }
}
