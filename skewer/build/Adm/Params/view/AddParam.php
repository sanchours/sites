<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 20.02.2017
 * Time: 12:26.
 */

namespace skewer\build\Adm\Params\view;

use skewer\components\ext\view\FormView;

class AddParam extends FormView
{
    public $aParamList;
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
            ->fieldString('group', \Yii::t('params', 'group'), ['readOnly' => true])
            ->fieldString('class', \Yii::t('params', 'class'), ['readOnly' => true])
            ->fieldSelect('name', \Yii::t('params', 'name'), $this->aParamList, [
                'forceSelection' => false,
                'allowBlank' => false,
                'editable' => true,
                'onUpdateAction' => 'getValueParams',
            ], false)

            ->fieldString('value', \Yii::t('params', 'value'))
            ->fieldString('title', \Yii::t('params', 'title'))
            ->fieldSelect('access_level', \Yii::t('params', 'access_level'), $this->aParametersList, [], false)
            ->field('show_val', \Yii::t('params', 'show_val'), 'text')
            ->setValue($this->aData)
            ->buttonSave()
            ->buttonBack();
    }
}
