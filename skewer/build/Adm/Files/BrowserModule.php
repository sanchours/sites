<?php

namespace skewer\build\Adm\Files;

use skewer\base\ui;

/**
 * Панель отображения набора файлов для панели выбора файлов
 * Class BrowserModule.
 */
class BrowserModule extends Module
{
    // возможность выбирать файлы
    protected $bCanSelect = true;

    protected $sListBuilderClass = 'ExtListModule';

    protected function preExecute()
    {
        $this->sectionId = $this->getInt('sectionId', $this->sectionId());
        parent::preExecute();
    }

    public function init()
    {
        // вызвать инициализацию, прописанной в родительском классе
        parent::init();

        // установить имя для используемого модуля
        $this->addLibClass('FileBrowserFiles');
    }

    /**
     * Установка служебных данных.
     *
     * @param ui\state\BaseInterface $oIface
     */
    protected function setServiceData(ui\state\BaseInterface $oIface)
    {
        // установить данные для передачи интерфейсу
        $oIface->setServiceData([
            'sectionId' => $this->sectionId(),
        ]);
    }
}
