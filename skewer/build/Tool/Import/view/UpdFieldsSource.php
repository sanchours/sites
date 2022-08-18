<?php


namespace skewer\build\Tool\Import\view;


use skewer\components\ext\view\FormView;
use skewer\components\import\Api;

class UpdFieldsSource extends FormView
{
    public $sSourceFile;
    public $sSourceStr;
    public $sGroup;
    public $iType;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('source_file', \Yii::t('import', 'field_source'), $this->iType != Api::Type_File ? 'hide' : 'file', ['groupTitle' => $this->sGroup])
            ->field('source_str', \Yii::t('import', 'field_source'), $this->iType == Api::Type_File ? 'hide' : 'string', ['groupTitle' => $this->sGroup])
            ->setValue([
                'source_file' => $this->sSourceFile,
                'source_str' => $this->sSourceStr,
            ]);
    }
}