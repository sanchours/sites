<?php

namespace skewer\components\ext;

use skewer\base\ui;
use skewer\build\Cms;

/**
 * Суперкласс для построителя ExtJS интерфейсов.
 */
abstract class ViewPrototype implements ui\state\BaseInterface
{
    /*
     * Работа с сообщениями
     */

    /** @var array набор сообщений */
    protected $aMessages = [
        'errors' => [],
        'messages' => [],
    ];

    /**
     * Добавить сообщение.
     *
     * @param $sHeader
     * @param $sText
     */
    public function addMessage($sHeader, $sText = '')
    {
        $this->aMessages['messages'][] = [$sHeader, $sText];
    }

    /**
     * Возвращает набор сообщений.
     *
     * @return array
     */
    protected function getMessages()
    {
        return $this->aMessages['messages'];
    }

    /**
     * Добавить сообщение об ошибке.
     *
     * @param $sText
     */
    public function addError($sText)
    {
        $this->aMessages['errors'][] = [$sText];
    }

    /**
     * Возвращает набор сообщений об ошибках.
     *
     * @return array
     */
    protected function getErrors()
    {
        return $this->aMessages['errors'];
    }

    /**
     * Работа Со Служебными Данными.
     */

    /**
     * @var array - набор служебных данных
     */
    protected $aServiceData = [];

    /**
     * Возвращает имя компонента.
     *
     * @abstract
     *
     * @return string
     */
    abstract public function getComponentName();

    /**
     * Вывод ошибки.
     *
     * @param string $sText
     * @param array $mData
     *
     * @return bool
     */
    protected function error($sText, $mData = [])
    {
        echo 'Error in ' . $this->getComponentName() . ': ' . $sText;
        if ($mData) {
            var_dump($mData);
        }

        return true;
    }

    /**
     * Задает массив со служебными данными для проброса
     * Этот массив вернется с посылкой.
     *
     * @param array $aData - массив данных
     */
    public function setServiceData($aData)
    {
        $this->aServiceData = $aData;
    }

    /**
     * Возвращает массив служебных данных.
     *
     * @return array
     */
    public function getServiceData()
    {
        return $this->aServiceData;
    }

    /**
     * Работа С Компонентами.
     */

    /**
     * Возвращает префикс JS библиотек.
     *
     * @return string
     */
    protected function getJSLibPrefix()
    {
        return 'Ext.Builder.';
    }

    /**
     * @var string - название компонента
     */
    protected $sComponentTitle = '';

    /**
     * Установить название компонента.
     *
     * @param $sTitle
     */
    public function setTitle($sTitle)
    {
        $this->sComponentTitle = $sTitle;
    }

    /**
     * Возвращает назвыние компонента.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->sComponentTitle;
    }

    /**
     * Набор параметров для перекрытия/дополнения стандартных в js.
     *
     * @var array
     */
    protected $aInitParams = [];

    /**
     * Добавляет инициализационный параметр для js слоя.
     *
     * @param $sName - имя параметра
     * @param $sValue - значение
     */
    public function setInitParam($sName, $sValue)
    {
        $this->aInitParams[$sName] = $sValue;
    }

    /**
     * Проверяет наличие инициализационного параметра.
     *
     * @param $sName
     *
     * @return bool
     */
    public function hasInitParam($sName)
    {
        return isset($this->aInitParams[$sName]);
    }

    /**
     * Удаляет инициализационный параметр
     *
     * @param $sName
     */
    public function delInitParam($sName)
    {
        if ($this->hasInitParam($sName)) {
            unset($this->aInitParams[$sName]);
        }
    }

    /**
     * Отдает инициализационный параметр
     *
     * @param $sName
     *
     * @return null|mixed
     */
    public function getInitParam($sName)
    {
        if ($this->hasInitParam($sName)) {
            return $this->aInitParams[$sName];
        }
    }

    /**
     * Отдает набор из всех инициализационных параметров модуля.
     *
     * @param Cms\Frame\ModulePrototype $oModule
     *
     * @return array
     */
    public function getAllInitParams(Cms\Frame\ModulePrototype $oModule)
    {
        // если есть заданные языковые метки
        if ($this->hasInitParam('lang')) {
            // подменить текущие языковые метки
            $this->setModuleLangValues(
                $oModule->parseLangVars($this->getInitParam('lang'))
            );
        } else {
            // проверить наличие глобавльных меток у модуля
            $aModuleParams = $oModule->getJSONHeader('init');
            if (isset($aModuleParams['lang'])) {
                $this->setModuleLangValues($aModuleParams['lang']);
            }
        }

        return $this->aInitParams;
    }

    /**
     * Задает набор языковых меток для модуля.
     *
     * @param array $aKeys набор псевдонимов языковых меток
     *
     * @return array
     */
    public function setModuleLangValues($aKeys)
    {
        $this->setInitParam('lang', $aKeys);
    }

    /**
     * Работа с заголовком
     */

    /**
     * Новое значение заголовка осноыной панели.
     *
     * @var string
     */
    protected $sNewTitle = '';

    /**
     * Меняет заголовок основной панели.
     *
     * @param $sNewTitle
     */
    public function setPanelTitle($sNewTitle)
    {
        $this->sNewTitle = $sNewTitle;
    }

    /**
     * Возвращает заголовок основной панели.
     *
     * @return string
     */
    public function getPanelTitle()
    {
        return $this->sNewTitle;
    }

    /**
     * @var array - набор дополнительных компонентов
     */
    protected $aComponents = [];

    /**
     * @var array - набор элементов управления
     */
    protected $aDockedItems = [];

    /**
     * Добавление компонента.
     *
     * @param $sComponentName
     */
    protected function addComponent($sComponentName)
    {
        // добавить, если еще нет
        if (!in_array($sComponentName, $this->aComponents)) {
            $this->aComponents[] = $sComponentName;
        }
    }

    /**
     * Запрос списка компонентов.
     *
     * @return array
     */
    protected function getComponents()
    {
        return $this->aComponents;
    }

    /**
     * Добавляет кнопку в интерфейс
     *
     * @param ui\element\Button $oButton
     */
    public function addButton(ui\element\Button $oButton)
    {
        $this->addExtButton(
            docked\AddBtn::create()
                ->setAction($oButton->getPhpAction())
                ->setTitle($oButton->getTitle())
                ->setIconCls($oButton->getIcon())
                ->setAddParamList($oButton->getAddParamList())
                ->setDirtyChecker($oButton->getDirtyChecker())
                ->setConfirm($oButton->getConfirm())
        );
    }

    /**
     * Работа С Кнопками Управления.
     *
     * @param docked\Prototype $oDocked
     * @param string $sPosition
     */
    public function addExtButton(docked\Prototype $oDocked, $sPosition = 'left')
    {
        // параметр $this передается для класса ext\docked\byUserFile
        /* @noinspection PhpMethodParametersCountMismatchInspection */
        $this->aDockedItems[$sPosition][] = $oDocked->getInitArray($this);
    }

    /**
     * Добавляет элемент управления к интерфейсу.
     *
     * @param $aItem
     * @param string $sPosition
     *
     * @return bool
     */
    public function addDockedItem($aItem, $sPosition = 'left')
    {
        $this->aDockedItems[$sPosition][] = $aItem;

        return true;
    }

    /**
     * Добавить кнопку "Отмена".
     *
     * @param string $sAction событие в php
     * @param string $sState событие в js
     * @param array $aParams дополнительные параметры при нажатии кнопки
     *
     * @return bool
     */
    public function addBtnCancel($sAction = 'init', $sState = 'init', $aParams = [])
    {
        return $this->addDockedItem([
            'text' => \Yii::t('adm', 'cancel'),
            'iconCls' => 'icon-cancel',
            'state' => $sState,
            'action' => $sAction,
            'addParams' => $aParams,
        ]);
    }

    /**
     * Кнопка "Выполнить действие" с подтверждением
     *
     * @param string $sAction
     * @param string $sText
     * @param string $sState
     *
     * @return bool
     */
    public function addBtnDo($sAction = 'init', $sText = '', $sState = 'allow_do')
    {
        return $this->addDockedItem([
            'text' => \Yii::t('adm', 'add'),
            'iconCls' => 'icon-add',
            'state' => $sState,
            'action' => $sAction,
            'actionText' => $sText,
        ]);
    }

    /**
     * Добавить кнопку "Добавить".
     *
     * @param string $sAction событие в php
     * @param string $sState событие в js
     * @param array $aParams дополнительные параметры при нажатии кнопки
     *
     * @return bool
     */
    public function addBtnAdd($sAction = 'addForm', $sState = 'addForm', $aParams = [])
    {
        return $this->addDockedItem([
            'text' => \Yii::t('adm', 'add'),
            'iconCls' => 'icon-add',
            'state' => $sState,
            'action' => $sAction,
            'addParams' => $aParams,
        ]);
    }

    public function addCustomBtnAdd($sAction = 'addForm', $sState = 'addForm', $aParams = [], $sText = '')
    {
        return $this->addDockedItem([
            'text' => $sText,
            'iconCls' => 'icon-add',
            'state' => $sState,
            'action' => $sAction,
            'addParams' => $aParams,
        ]);
    }

    /**
     * Добавить кнопку "Сохранить".
     *
     * @param string $sAction событие в php
     * @param string $sState событие в js
     * @param array $aParams дополнительные параметры при нажатии кнопки
     */
    public function addBtnSave($sAction = 'save', $sState = 'save', $aParams = [])
    {
        $this->addExtButton(
            docked\Api::create(\Yii::t('adm', 'save'))
                ->setIconCls(docked\Api::iconSave)
                ->setState($sState)
                ->setAction($sAction)
                ->setAddParamList($aParams)
                ->unsetDirtyChecker()
        );
    }

    /**
     * Добавить кнопку "Удалить".
     *
     * @param string $sAction событие в php
     * @param string $sState событие в js
     * @param array $aParams дополнительные параметры при нажатии кнопки
     *
     * @return bool
     *
     * @deprecated use buttonDelete
     */
    public function addBtnDelete($sAction = 'delete', $sState = 'delete', $aParams = [])
    {
        return $this->addDockedItem([
            'text' => \Yii::t('adm', 'del'),
            'iconCls' => 'icon-delete',
            'state' => $sState,
            'action' => $sAction,
            'addParams' => $aParams,
            'unsetFormDirtyBlocker' => true,
        ]);
    }

    /**
     * Добавить разделитель кнопок.
     *
     * @param string $sSeparator - тип разделителя
     *
     * @return bool
     */
    public function addBtnSeparator($sSeparator = '-')
    {
        return $this->addDockedItem($sSeparator);
    }

    /**
     * Возвращает набор элементов управления.
     *
     * @return array
     */
    protected function getDockedItems()
    {
        return $this->aDockedItems;
    }

    /**
     * Интерфейсные Данные.
     */

    // флаг необходимости перезагрузки
    protected $bDoNotReload = false;

    /**
     * Установить флог необходимости перезагруки.
     *
     * @param bool $bVal
     */
    public function setDoNotReload($bVal = true)
    {
        $this->bDoNotReload = $bVal;
    }

    /**
     * Возвращает флаг необходимость переинициализации.
     *
     * @return bool
     */
    protected function getDoNotReload()
    {
        return $this->bDoNotReload;
    }

    /*
     * Дополнительный текст
     */

    /** @var string дополнительный текст перед панелью */
    protected $sAddText = '';

    /**
     * Отдает дополнительный текст перед панелью.
     *
     * @return string
     */
    public function getAddText()
    {
        return $this->sAddText;
    }

    /**
     * Задет дополнительный текст перед панелью.
     *
     * @param string $addText
     */
    public function setAddText($addText)
    {
        $this->sAddText = $addText;
    }

    /**
     * Сборка Интерфейса.
     */

    /** @var array[] набор дополнительных библиотек */
    protected $aAddLibs = [];

    /**
     * Добавить определение js библиотеки в вывод.
     * Вызовет при финальной инициализации одноименную функцию админского модуля.
     *
     * @param string $sLibName имя библиотеки
     * @param string $sLayerName слой
     * @param string $sModuleName имя модуля
     */
    public function addLibClass($sLibName, $sLayerName = '', $sModuleName = '')
    {
        $this->aAddLibs[] = [
            'name' => $sLibName,
            'layer' => $sLayerName,
            'module' => $sModuleName,
        ];
    }

    /**
     * Отдает интерфейсный массив для атопостроителя интерфейсов.
     *
     * @abstract
     *
     * @return array
     */
    abstract public function getInterfaceArray();

    /**
     * Задает инициализационный  массив для атопостроителя интерфейсов.
     *
     * @param Cms\Frame\ModulePrototype $oModule - ссылка на вызвавший объект
     */
    public function setInterfaceData(Cms\Frame\ModulePrototype $oModule)
    {
        // установить данные для работы с библиотекой
        $oModule->setJSONHeader('layerName', 'Builder');
        $oModule->setJSONHeader('externalLib', 'Builder');

        // подключаем ExtBuilder
        $bundle = Asset::register(\Yii::$app->view);

        $oModule->setJSONHeader('externalLibDir', $bundle->baseUrl);

        // дополнительные библиотеки
        foreach ($this->aAddLibs as $aAddLib) {
            $oModule->addLibClass($aAddLib['name'], $aAddLib['layer'], $aAddLib['module']);
        }

        // данные для автопостроителя
        $aInterface = $this->getInterfaceArray();

        // добавление компонентов ( текущий и то, что модуль задал )
        $this->addComponent($this->getComponentName());
        // подчиненные библиотеки
        $aInterface['subLibs'] = $this->getComponents();
        // имя компонента
        $aInterface['extComponent'] = $this->getComponentName();
        // служебные данные
        $aInterface['serviceData'] = (object) $this->getServiceData();
        // название компонента
        $aInterface['componentTitle'] = $this->getTitle();
        // новое название панели
        $aInterface['panelTitle'] = $this->getPanelTitle();
        // флаг необходимости перезагрузки
        $aInterface['doNotReload'] = $this->getDoNotReload();
        // инициализационные параметры для элемента
        $aInterface['init'] = $this->getAllInitParams($oModule);
        // дополнительный текст
        $aInterface['addText'] = $this->getAddText();

        // сообщения
        $aMessages = array_merge($this->getMessages(), $oModule->getMessages());
        if ($aMessages) {
            $aInterface['pageMessages'] = $aMessages;
        }

        // ошибки
        $aErrors = array_merge($this->getErrors(), $oModule->getErrors());
        if ($aErrors) {
            $aInterface['pageErrors'] = $aErrors;
        }

        // сообщения забрали - очистить списки
        $oModule->clearMessages();

        // элементы управления
        $aInterface['dockedItems'] = $this->getDockedItems();

        // добавить в вывод все поля
        foreach ($aInterface as $sIntRowName => $mIntRow) {
            $oModule->setData($sIntRowName, $mIntRow);
        }
    }
}
