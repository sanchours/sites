<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 05.12.2016
 * Time: 16:48.
 */

namespace skewer\build\Adm\Order\view;

use skewer\components\ext\view\FormView;

class StatusShow extends FormView
{
    public $bIsSystemMode;
    public $bIsNewRecord;
    public $aLanguages;
    public $sLabel;
    public $aData;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        if ($this->bIsSystemMode || !$this->bIsNewRecord) {
            $this->_form->field('name', \Yii::t('order', 'status_name'), $this->bIsNewRecord ? 'string' : 'hide');
        }

        foreach ($this->aLanguages as $aLanguage) {
            $this->_form->fieldString('title_' . $aLanguage['name'], \Yii::t('order', $this->sLabel, [$aLanguage['title']]));
        }

        $this->_form->field('send_user', \Yii::t('order', 'status_send_user'), 'check');
        $this->_form->field('send_admin', \Yii::t('order', 'status_send_admin'), 'check');

        $this->_form->setValue($this->aData)
            ->buttonSave('StatusSave')
            ->buttonBack('StatusList');
    }
}
