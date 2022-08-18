<?php

namespace skewer\build\Tool\SeoGen\view;

use skewer\build\Tool\SeoGen\exporter\Api as ExporterApi;
use skewer\components\ext\view\FormView;

class ExportFormSettings extends FormView
{
    /**
     * @var string Тип данных
     */
    public $sTypeData = '';

    /**
     * @var array Значения полей формы
     */
    public $aValues = [];

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->headText('<h1>' . \Yii::t('SeoGen', 'export_headTitle') . '</h1>')
            ->fieldSelect('data_type', 'Тип данных', ExporterApi::getListTitleExporters(), ['onUpdateAction' => 'updateExportState']);

        if ($this->sTypeData) {
            $oExporter = ExporterApi::getExporterByAlias($this->sTypeData);
            $oExporter->buildFieldInForm($this->_form);
        }

        $this->_form
            ->button('exportRun', 'Экспортировать', 'icon-reload', 'save', ['unsetFormDirtyBlocker' => true])
            ->button('showLastLog', 'Лог последнего запуска', '')
            ->buttonBack()

            ->setValue($this->aValues);
    }

    public function beforeBuild()
    {
        // Убираем данные, которые не должны протягиваться между состояниями
        foreach ($this->aValues as $key => &$item) {
            if (!in_array($key, ['data_type'])) {
                unset($this->aValues[$key]);
            }
        }
    }
}
