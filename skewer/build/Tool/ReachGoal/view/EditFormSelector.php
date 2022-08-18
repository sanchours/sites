<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 20.01.2017
 * Time: 11:48.
 */

namespace skewer\build\Tool\ReachGoal\view;

use skewer\components\ext\view\FormView;
use skewer\components\targets;

class EditFormSelector extends FormView
{
    public $aTypes;
    public $aParams;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('id', 'ID', 'hide')
            ->field('title', \Yii::t('ReachGoal', 'field_title_selector'), 'string')
            ->field('selector', \Yii::t('ReachGoal', 'field_selector'), 'string')
            ->field('old_selector', \Yii::t('ReachGoal', 'field_selector'), 'hide');
        foreach ($this->aTypes as $type) {
            $this->_form->fieldSelect(
                mb_strtolower($type) . '_target',
                \Yii::t('ReachGoal', 'field_' . mb_strtolower($type) . '_target'),
                targets\models\Targets::getByTypeArray($type)
        );
        }
        $this->_form->buttonSave('SaveSelector')
            ->buttonBack('ShowSelectors')
            ->setValue($this->aParams);
    }
}
