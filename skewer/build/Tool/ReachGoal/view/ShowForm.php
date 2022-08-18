<?php
/**
 * Created by PhpStorm.
 * User: ram
 * Date: 16.05.2018
 * Time: 15:59.
 */

namespace skewer\build\Tool\ReachGoal\view;

use skewer\components\ext\view\FormView;
use skewer\components\targets\models\Targets;

class ShowForm extends FormView
{
    /** @var Targets */
    public $targetRow;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $aNameParams = [];
        if ($this->targetRow->id !== null) {
            $aNameParams['disabled'] = 'disabled';
        }

        $this->_form
            ->field('id', 'ID', 'hide')
            ->field('title', \Yii::t('ReachGoal', 'field_title'), 'string')
            ->fieldIf($this->targetRow->isGoogle(), 'category', \Yii::t('ReachGoal', 'field_category'), 'string')
            ->field('name', \Yii::t('ReachGoal', 'field_name'), 'string', $aNameParams)
            ->field('type', \Yii::t('ReachGoal', 'field_type'), 'hide')
            ->buttonSave()
            ->buttonBack();

        if ($this->targetRow->id) {
            $this->_form
                ->buttonSeparator('->')
                ->buttonDelete();
        }

        $this->_form->setValue($this->targetRow->getAttributes());
    }
}
