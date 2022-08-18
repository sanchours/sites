<?php

namespace skewer\build\Adm\Params\view;

use skewer\components\ext\view\FormView;

class ExportResult extends FormView
{
    public $fileName;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_form
            ->headText("<a href='{$this->fileName}' target='_blank'>{$this->fileName}</a>")
            ->buttonCancel();
    }
}
