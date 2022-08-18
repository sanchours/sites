<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 16.01.2017
 * Time: 10:06.
 */

namespace skewer\build\Tool\Copyright\view;

use skewer\components\ext\view\FormView;

class EditModule extends FormView
{
    public $sFieldActivityTitle;
    public $sFieldDisableInTitle;
    public $aAllSections;
    public $aSectionsWithDisableCopyrightModule;
    public $sFieldTextTitle;
    public $sActivityModule;
    public $sTemplatedText;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldCheck('activity', $this->sFieldActivityTitle)
            ->fieldMultiSelect(
                'disabledSection',
                $this->sFieldDisableInTitle,
                $this->aAllSections,
                $this->aSectionsWithDisableCopyrightModule
            )
            ->fieldWysiwyg('text', $this->sFieldTextTitle)
            ->setValue(
                [
                    'activity' => $this->sActivityModule,
                    'text' => $this->sTemplatedText,
                ]
            )
            ->buttonSave('save');
    }
}
