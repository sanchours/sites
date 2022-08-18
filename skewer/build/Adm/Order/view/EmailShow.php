<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 22.11.2016
 * Time: 16:33.
 */

namespace skewer\build\Adm\Order\view;

use skewer\components\ext\view\FormView;

class EmailShow extends FormView
{
    public $sLanguageFilter;
    public $aLanguages;
    public $aItems;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        if (count($this->aLanguages) > 1) {
            $this->_form
                ->filterSelect('filter_language', $this->aLanguages, $this->sLanguageFilter, \Yii::t('adm', 'language'), ['set' => true])
                ->setFilterAction('emailShow');
        }

        $this->_form
            ->field('info', '', 'show', ['hideLabel' => true])
            ->fieldString('title_user_mail', \Yii::t('order', 'mail_title_to_user'))
            ->field('user_content', \Yii::t('order', 'mail_to_user'), 'wyswyg')
            ->fieldString('title_adm_mail', \Yii::t('order', 'mail_title_to_admin'))
            ->field('adm_content', \Yii::t('order', 'mail_to_admin'), 'wyswyg')
            ->fieldString('title_change_status_mail', \Yii::t('order', 'mail_title_to_change_status'))
            ->field('status_content', \Yii::t('order', 'mail_to_change_status'), 'wyswyg')
            ->fieldString('title_status_paid', \Yii::t('order', 'mail_title_to_change_status_on_paid'))
            ->field('status_paid_content', \Yii::t('order', 'mail_to_change_status_on_paid'), 'wyswyg')
            ->fieldString('title_change_order', \Yii::t('order', 'mail_to_title_change_order'))
            ->field('content_change_order', \Yii::t('order', 'mail_to_content_change_order'), 'wyswyg')
            ->setValue($this->aItems)
            ->buttonSave('emailSave')
            ->buttonCancel();
    }
}
