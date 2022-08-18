<?php

namespace skewer\build\Adm\Forms\view;

use skewer\components\ext\view\FormView;

class Form extends FormView
{
    public $formsForSelection;
    public $formInfo;
    public $formTitles;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $i = 0;
        foreach ($this->formInfo as $sGroup => $aValue) {
            $this->_form
                ->fieldSelect(
                    "form_{$sGroup}",
                    rtrim(\Yii::t('forms', 'select_form') . $this->formTitles[$i]),
                    $this->formsForSelection,
                    ['cls' => 'sk-select-form'],
                    false
                )
                ->fieldIf(
                    $aValue['link'],
                    "form_{$sGroup}_link",
                    \Yii::t('forms', 'link_title'),
                    'show'
                )
                ->setValue([
                    "form_{$sGroup}" => $aValue['id'],
                    "form_{$sGroup}_link" => $aValue['link'],
                    ]);
            ++$i;
        }

        $this->_form->buttonSave('linkFormToSection');
    }
}
