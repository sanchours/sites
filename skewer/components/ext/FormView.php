<?php

namespace skewer\components\ext;

use skewer\base\ft as ft;
use skewer\base\orm;
use skewer\base\ui;
use skewer\build\Cms;
use skewer\components\ActiveRecord\ActiveRecord;

/**
 * Класс для автоматической сборки админских интерфейсов на ExtJS
 * Класс для построения форм
 *
 * @class: ExtForm
 * @project Skewer
 *
 * @Author: sapozhkov
 * @version: $Revision$
 * @date: $Date$
 */
class FormView extends ModelPrototype implements ui\state\EditInterface
{
    const CheckSuffix = '__check__';

    /** @var bool флаг использования спец директории для изображений модуля */
    protected $bUseSpecSectionForImages = false;

    /** @var int id спец директории для загружаемых изображений */
    protected $iSpecSectionForImages = 0;

    /**
     * Имя состояния для сохранения.
     *
     * @var string
     */
    private $sSaveState = 'save';

    /** @var bool Отслеживать изменения */
    protected $bTrackChanges = true;

    /**
     * Устанавливает флаг использования спец директории для изображений модуля.
     *
     * @param int $iId
     */
    public function useSpecSectionForImages($iId = 0)
    {
        $this->bUseSpecSectionForImages = true;
        $this->iSpecSectionForImages = (int) $iId;
    }

    /**
     * Общие Функции.
     */

    /**
     * Возвращает имя компонента.
     *
     * @return string
     */
    public function getComponentName()
    {
        return 'Form';
    }

    /**
     * Запрос дополнительных полей для инициализации полей по ft модели.
     *
     * @param ft\model\Field $oField
     *
     * @return array
     */
    protected function getAddParamsForFtField(ft\model\Field $oField)
    {
        return [
            'disabled' => false,
            'activeError' => $oField->getParameter('form_error'),
        ];
    }

    /**
     * Добавляет к текущей модели запись.
     *
     * @param field\Prototype $oItem новая запись для модели
     *
     * @return bool
     */
    public function addField(field\Prototype $oItem)
    {
        // проверка корректности описания
        if (!$oItem->getName()) {
            $this->error('Model create. Wrong input - no name field found.', $oItem->getDesc());

            return false;
        }

        if (!$oItem->getView()) {
            $this->error('Model create. Wrong input - no view field found.', $oItem->getDesc());

            return false;
        }

        return parent::addField($oItem);
    }

    /**
     * Преобразует объект поля в пригодный для ExtJS массив.
     *
     * @param field\Prototype $oField
     *
     * @return array
     */
    public static function getFieldDesc(field\Prototype $oField)
    {
        $oField->setDescVal('type', $oField->getView());

        // убрать ненужное поле
        $oField->delDescVal('view');

        // значение поля - обязательное ( может быть false / 0 / null / ... )
        if (!$oField->hasValue()) {
            $oField->setValue('');
        }

        // название - обязательное, если не указано иное
        if (!$oField->getTitle() and $oField->getTitle() !== false) {
            $oField->setTitle($oField->getName());
        }

        // преобразование значения поля
        $sValue = $oField->getValue();
        $oField->setValue($sValue);

        return $oField->getDesc();
    }

    /**
     * Устанавливает значения для набор элементов.
     *
     * @param array|orm\ActiveRecord $aValues - набор пар имя поля - значение
     */
    public function setValues($aValues)
    {
        if ($aValues instanceof orm\ActiveRecord) { // Старый ActiveRecord
            $aValues = $aValues->getData(false);
        }

        if ($aValues instanceof ActiveRecord) { // yii ActiveRecord
            $aValues = $aValues->getAttributes();
        }

        //Перебираем именно поля формы чтобы работали магически __get и __set для AR
        foreach ($this->aFields as $oField) {
            if (isset($aValues[$oField->getName()])) {
                $oField->setValue($aValues[$oField->getName()]);
            }
        }
    }

    /**
     * Устанавливает значения по умолчанию.
     */
    public function setDefaultValues()
    {
        foreach ($this->getFields() as $oItem) {
            $oItem->setValue($oItem->getDefaultVal());
        }
    }

    /**
     * Протокол Передачи Данных.
     */

    /**
     * Собирает интерфейсный массив для выдачи в JS.
     *
     * @return array
     */
    public function getInterfaceArray()
    {
        // собираем массив описаний
        $aItems = [];
        foreach ($this->getFields() as $oItem) {
            $aItems[] = $this->getFieldDesc($oItem);
        }

        // выходной массив
        $aOut = [
            'items' => $aItems,
            'saveStateName' => $this->getSaveState(),
            'trackChanges' => $this->getTrackChanges(),
            'barElements' => $this->getFilters(),
            'actionNameLoad' => $this->getPageLoadActionName(),
        ];

        // вывод данных
        return $aOut;
    }

    /**
     * Возввращает массив инициализации специфического поля
     * По сути расширяет массив $aInitParams дополнительными параметрам, которые будут
     *      приняты js кодом и обработаны.
     *
     * @param string $sLibName - имя спец класса
     * @param array $aInitParams - параметры для передачи
     *
     * @return array
     */
    public function getSpecificItemInitArray($sLibName, $aInitParams = [])
    {
        // добавить инициализацию библиотеки
        $this->addLibClass($sLibName);

        // метка спец обработчика
        $aInitParams['view'] = 'specific';

        // имя для библиотеки наследования
        $aInitParams['extendLibName'] = $sLibName;

        return $aInitParams;
    }

    /**
     * Помечает значения элементов массива, если есть одинаковые.
     *
     * @param array $aData
     *
     * @return array
     */
    public static function markUniqueValue($aData = [])
    {
        // если есть дублируюшие элементы
        if (count(array_values($aData)) != count(array_values(array_unique($aData)))) {
            $aResult = [];
            $aFound = [];
            foreach ($aData as $k => $v) {
                $val = $v;
                // если был такой элемент - дополнить
                if (in_array($val, $aFound)) {
                    $val = sprintf('%s [%s]', $v, $k);
                    // это на всякий пожарный )
                    if (in_array($val, $aFound)) {
                        $val = sprintf('%s [%s/%d]', $v, $k, random_int(10000000, 99999999));
                    }
                }
                $aFound[] = $val;
                $aResult[$k] = $val;
            }

            return $aResult;
        }

        return $aData;
    }

    /**
     * Отдает описание для создания прописанного в js библиотеке поля.
     *
     * @static
     *
     * @param string $sLibName
     * @param array $aAddData
     *
     * @return array
     */
    public static function getDesc4CustomField($sLibName, $aAddData = [])
    {
        return array_merge([
            'customField' => $sLibName,
        ], $aAddData);
    }

    /**
     * Задает инициализационный  массив для атопостроителя интерфейсов.
     *
     * @param Cms\Frame\ModulePrototype $oModule - ссылка на вызвавший объект
     */
    public function setInterfaceData(Cms\Frame\ModulePrototype $oModule)
    {
        // если есть спец флаг
        if ($this->bUseSpecSectionForImages) {
            // увязывание файлов для wyswyg в спец директорию
            foreach ($this->aFields as $oField) {
                if ($oField->getView() === 'wyswyg') {
                    $aAddConfig = $oField->getDescVal('addConfig', []);
                    $aAddConfig['filebrowserBrowseUrl'] = $this->getFileBrowserUrl($oModule);
                    $oField->setDescVal('addConfig', $aAddConfig);
                }
            }
        }

        // выполняем родительскую часть модуля
        parent::setInterfaceData($oModule);
    }

    /**
     * Формируют массив и устанавливает служебные данные для замены полей инициализированного интерфейса.
     *
     * @param Cms\Frame\ModulePrototype $oModule - ссылка на вызвавший объект
     */
    public function setInterfaceDataUpd(Cms\Frame\ModulePrototype $oModule)
    {
        $aItems = [];
        foreach ($this->getInterfaceArray()['items'] as $aItem) {
            $aItems[$aItem['name']] = $aItem;
        }

        $oModule->setData('cmd', 'loadItem');
        $oModule->setData('items', $aItems);
    }

    /**
     * Отдает ссылку на интерфейс загрузки файлов с автовыбором директории для модуля.
     *
     * @param Cms\Frame\ModulePrototype $oModule
     *
     * @return string
     */
    public function getFileBrowserUrl(Cms\Frame\ModulePrototype $oModule)
    {
        $sPattern = '/oldadmin/?mode=fileBrowser&%s=%s&type=file&returnTo=ckeditor';
        if ($this->iSpecSectionForImages) {
            return sprintf($sPattern, 'section', $this->iSpecSectionForImages);
        }

        $sModuleName = sprintf('%s_%s', $oModule->getLayerName(), $oModule->getModuleName());

        return sprintf($sPattern, 'module', $sModuleName);
    }

    /**
     * Отдает набор полей для вывода по умолчанию.
     *
     * @return string
     */
    protected function getDefaultFieldsSet()
    {
        return '';
    }

    /**
     * Отдает состояние для сохранения.
     *
     * @return string
     */
    private function getSaveState()
    {
        return $this->sSaveState;
    }

    /**
     * задает имя состояния для сохранения.
     *
     * @param $sState
     */
    public function setSaveState($sState)
    {
        $this->sSaveState = $sState;
    }

    /**
     * @param bool $bTrackChanges
     */
    public function setTrackChanges($bTrackChanges)
    {
        $this->bTrackChanges = $bTrackChanges;
    }

    /**
     * @return bool
     */
    public function getTrackChanges()
    {
        return $this->bTrackChanges;
    }

    /**
     * Установка расширенной кнопки в интерфейсе.
     *
     * @param $oButton
     *
     * @return $this
     */
    public function buttonCustomExt(docked\Prototype $oButton)
    {
        $this->addExtButton($oButton);

        return $this;
    }
}
