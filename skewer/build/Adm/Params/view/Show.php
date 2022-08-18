<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 20.02.2017
 * Time: 12:44.
 */

namespace skewer\build\Adm\Params\view;

use skewer\components\ext\view\FormView;

class Show extends FormView
{
    public $aAllGroups;
    public $aParams4Module;
    public $bInDataValTypeNotObj;
    public $aParametersList;
    public $aData;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldHide('id', 'id')
            ->fieldHide('parent', \Yii::t('params', 'parent'))
            ->fieldSelect('group', \Yii::t('params', 'group'), $this->aAllGroups, [
                'forceSelection' => false,
                'allowBlank' => false,
                'editable' => true,
                'onUpdateAction' => 'getModuleParams',
            ], false)
            ->fieldString('class', \Yii::t('params', 'class'), ['readOnly' => true])
            ->fieldSelect('name', \Yii::t('params', 'name'), $this->aParams4Module, [
                'forceSelection' => false,
                'allowBlank' => false,
                'editable' => true,
                'onUpdateAction' => 'getValueParams',
            ], false);

        if ($this->bInDataValTypeNotObj) {
            $this->_form->field('value', \Yii::t('params', 'value'));
        }

        $this->_form
            ->fieldString('title', \Yii::t('params', 'title'))
            ->fieldSelect('access_level', \Yii::t('params', 'access_level'), $this->aParametersList, [], false)
            ->field('show_val', \Yii::t('params', 'show_val'), 'text')
            ->field('sParamsListGroup', \Yii::t('params', 'paramList'), 'show', ['labelAlign' => 'top'])
            ->setValue($this->aData)
            ->buttonSave('saveAndEdit', \Yii::t('params', 'save_and_continue'), ['unsetFormDirtyBlocker' => true])
            ->buttonSave()
            ->buttonBack();
    }
}
