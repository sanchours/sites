<?php

namespace skewer\build\Tool\SeoGen\view;

use skewer\build\Tool\LeftList\ModulePrototype;
use skewer\build\Tool\SeoGen\importer\Api as ImporterApi;
use skewer\components\ext\view\FormView;

class ImportFormSettings extends FormView
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
     * @var ModulePrototype Ссылка на модуль
     */
    protected $oModule;

    public function __construct($oModule, array $config = [])
    {
        $this->oModule = $oModule;
        parent::__construct($config);
    }

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $sHeadText = $this->oModule->renderTemplate(
            'head.php',
            ['headTitle' => \Yii::t('SeoGen', 'import_headTitle')]
        );

        $this->_form
            ->headText($sHeadText)
            ->field('file', 'Файл', 'file')
            ->fieldSelect('data_type', 'Тип данных', ImporterApi::getListTitleImporters(), ['onUpdateAction' => 'updateImportState']);

        if ($this->sTypeData) {
            $oImporter = ImporterApi::getImporterByAlias($this->sTypeData);
            $oImporter->buildFieldInForm($this->_form);
        }

        $this->_form
            ->button('importRun', 'Импорт', 'icon-reload', 'save', ['unsetFormDirtyBlocker' => true])
            ->button('showLastLog', 'Лог последнего запуска', 'icon-list')
            ->buttonBack()

            ->setValue($this->aValues);
    }

    public function beforeBuild()
    {
        // Убираем данные, которые не должны протягиваться между состояниями
        foreach ($this->aValues as $key => &$item) {
            if (!in_array($key, ['file', 'data_type', 'enable_staticContents'])) {
                unset($this->aValues[$key]);
            }
        }
    }
}
