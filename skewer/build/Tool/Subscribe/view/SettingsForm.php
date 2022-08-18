<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 27.01.2017
 * Time: 12:30.
 */

namespace skewer\build\Tool\Subscribe\view;

use skewer\components\auth\CurrentAdmin;
use skewer\components\ext\view\FormView;

class SettingsForm extends FormView
{
    public $aModes;
    public $aSubscribeMode;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSelect('mode', \Yii::t('subscribe', 'subscribe_mode'), $this->aModes, [], false);

        if (CurrentAdmin::isSystemMode()) {
            $this->_form->fieldInt('iLimit', \Yii::t('subscribe', 'subscribe_limit'));
        }

        $this->_form->buttonSave('saveSettings')
            ->buttonCancel('users')
            ->setValue($this->aSubscribeMode);
    }
}
