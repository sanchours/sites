<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 20.02.2017
 * Time: 14:03.
 */

namespace skewer\build\Adm\Params\view;

use skewer\components\ext\view\FormView;

class GetModuleParams extends FormView
{
    public $aParams;
    public $sClass;
    public $sParamsListGroup;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldString('class', \Yii::t('params', 'class'), ['readOnly' => true])
            ->fieldSelect('name', \Yii::t('params', 'name'), $this->aParams, ['editable' => true], false)
            ->field('sParamsListGroup', \Yii::t('params', 'paramList'), 'show', ['labelAlign' => 'top'])
            ->setValue([
                'class' => $this->sClass,
                'sParamsListGroup' => $this->sParamsListGroup,
            ]);
    }
}
