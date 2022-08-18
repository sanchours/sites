<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 26.01.2017
 * Time: 17:42.
 */

namespace skewer\build\Tool\Schedule\view;

use skewer\build\Tool\Schedule\Api;
use skewer\components\ext\view\FormView;

class Show extends FormView
{
    public $aPriorityArray;
    public $aResourceArray;
    public $aTargetArray;
    public $aStatusArray;
    public $iItemId;
    public $aItem;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldHide('id', 'id')
            ->fieldString('title', \Yii::t('schedule', 'title'))
            ->fieldString('name', \Yii::t('schedule', 'name'))
            ->fieldString('command', \Yii::t('schedule', 'command'))
            ->fieldSelect('priority', \Yii::t('schedule', 'priority'), $this->aPriorityArray, [], false)
            ->fieldSelect('resource_use', \Yii::t('schedule', 'resource_use'), $this->aResourceArray, [], false)
            ->fieldSelect('target_area', \Yii::t('schedule', 'target_area'), $this->aTargetArray, [], false);

        Api::addRunTimeSettings($this->_form)
            ->buttonSave()
            ->buttonCancel();

        $this->_form->buttonConfirm('tryTask', \Yii::t('schedule', 'try_task'), \Yii::t('schedule', 'try_task_confirm'), 'icon-save');

        if ($this->iItemId) {
            $this->_form->buttonSeparator('->')->buttonDelete();
        }
        $this->_form->setValue($this->aItem);
    }
}
