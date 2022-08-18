<?php

namespace skewer\build\Cms\Tabs;

use skewer\base\queue\Task;
use skewer\base\ui;
use skewer\build\Cms;
use skewer\components\ext;
use skewer\components\ext\ListRows;
use yii\helpers\ArrayHelper;

/**
 * Протитип модулей на основе автопостроителя.
 */
abstract class ModulePrototype extends Cms\Frame\ModulePrototype
{
    /**
     * Имя модуля.
     *
     * @var string
     */
    protected $sTabName = '';

    /**
     * Отдает название модуля.
     *
     * @return string
     */
    public function getTitle()
    {
        if (!$this->sTabName) {
            return $this->title;
        }

        return \Yii::t($this->getCategoryMessage(), $this->sTabName); //используется ли это вариант???
    }

    /**
     * Имя панели.
     *
     * @var string
     */
    protected $sPanelName = '';

    /**
     * Массив внутренних данных.
     * Работает на внутреннем механизме хранения состояний.
     * Данные будут сброшены как только вкладка будет закрыта/перезагружена
     * Для работы используются методы getInnerData, setInnerData, hasInnerData.
     *
     * @var mixed[]
     */
    protected $aInnerData = [];

    /**
     * Отдает данные из внутреннего сессионного хранилища.
     * Данные будут сброшены как только вкладка будет закрыта/перезагружена.
     *
     * @param string $sName имя параметра
     * @param string $mDefault значение по умолчанию, если не найдено в хранилище
     *
     * @return mixed
     */
    public function getInnerData($sName, $mDefault = '')
    {
        if (isset($this->aInnerData[$sName])) {
            return $this->aInnerData[$sName];
        }

        return $mDefault;
    }

    /**
     * Отдает данные из внутреннего сессионного хранилища, приведенные к int.
     * Если данных нет, отдает $mDefault как есть, даже если она не инт
     * Данные будут сброшены как только вкладка будет закрыта/перезагружена.
     *
     * @param string $sName имя параметра
     * @param mixed $mDefault значение по умолчанию, если не найдено в хранилище
     *
     * @return int
     */
    public function getInnerDataInt($sName, $mDefault = 0)
    {
        if ($this->hasInnerData($sName)) {
            return (int) $this->getInnerData($sName);
        }

        return $mDefault;
    }

    /**
     * Сохраняет данные во внутреннее хранилище значений
     * Данные будут сброшены как только вкладка будет закрыта/перезагружена.
     *
     * @param $sName
     * @param $mValue
     */
    public function setInnerData($sName, $mValue)
    {
        $this->aInnerData[$sName] = $mValue;
    }

    /**
     * Отдает флаг наличия данных во внутреннем хранилище по имени
     * Данные будут сброшены как только вкладка будет закрыта/перезагружена.
     *
     * @param string $sName имя параметра
     *
     * @return bool
     */
    public function hasInnerData($sName)
    {
        return isset($this->aInnerData[$sName]);
    }

    /**
     * Устанавливает название панели.
     *
     * @param string $sNewName - новое имя
     * @param bool $bAddTabName - нужно ли добавить в начало имя вкладки
     */
    public function setPanelName($sNewName, $bAddTabName = false)
    {
        $sPrefix = $bAddTabName && $this->getTitle() ? $this->getTitle() : '';
        $sDelimiter = $sPrefix && $sNewName ? ': ' : '';
        $this->sPanelName = $sPrefix . $sDelimiter . $sNewName;
    }

    /**
     * Состояние при инициализации вкладки.
     */
    public function actionInitTab()
    {
        $oTab = new ext\EmptyView();

        // флаг инициализации вкладки
        $this->setData('initTabFlag', true);

        $this->setInterface($oTab);
    }

    /**
     * Установка служебных данных.
     *
     * @param ui\state\BaseInterface $oIface
     */
    protected function setServiceData(ui\state\BaseInterface $oIface)
    {
    }

    /**
     * Добавляет данные для вывода в шаблонизатор
     *
     * @param ui\state\BaseInterface $oInterface - модуль, для которого идет вызов
     *
     * @return bool
     */
    final public function setInterface(ui\state\BaseInterface $oInterface)
    {
        // установка заголовков
        $oInterface->setTitle($this->getTitle());
        $oInterface->setPanelTitle($this->sPanelName ? $this->sPanelName : $this->getTitle());

        // установка служебных данных из модуля для передачи
        $this->setServiceData($oInterface);

        // добавление интерфейсных данных в посылку
        $oInterface->setInterfaceData($this);

        return true;
    }

    /**
     * Добавляет данные для замены в инициализированной форме.
     *
     * @param ext\FormView $oForm Интерфейс формы
     *
     * @return bool
     */
    final public function setInterfaceUpd(ext\FormView $oForm)
    {
        // Добавление интерфейсных данных в посылку
        $oForm->setInterfaceDataUpd($this);

        return true;
    }

    /**
     * Отдает флаг фозможности создания наследников для данного модуля
     * в дереве процессов.
     *
     * @return bool
     */
    protected function canBeParent()
    {
        return false;
    }

    /**
     * Построение вида (view).
     *
     * @param ext\view\Prototype $oView
     */
    protected function render($oView)
    {
        $oView->setModule($this);

        $oView->beforeBuild();

        $oView->build();

        $oView->afterBuild();

        $this->setInterface($oView->getInterface());
    }

    /**
     * Отправляет в интерфейс команду на перестрение одной строки в списке (используется вместо метода ui\StateBuilder::updRow()).
     *
     * @param array $aData данные
     * @param array|string $mSearchFields имя колонки (или набор) по которой будет произведено сравнение
     *
     * @return ext\ListRows
     */
    protected function updateRow($aData, $mSearchFields = 'id')
    {
        $oListVal = new ListRows();
        $oListVal->setSearchField($mSearchFields);
        $oListVal->addDataRow($aData);
        $oListVal->setData($this);

        return $oListVal;
    }

    /**
     * Запуск задачи c перезапуском
     *
     * @param array $aConfig - конфиг задачи
     * @param string $sAction - action на который будет стучаться задача
     * @param bool $bRunTaskByClassName - запускать существующую задачу по className, если по id задача не найдена?
     *
     * @return array - массив со статусом и id задачи
     */
    protected function runTaskWithReboot($aConfig, $sAction, $bRunTaskByClassName = false)
    {
        $aData = $this->getInData();

        if (empty($aData) && $this->get('params')) {
            $aData = $this->get('params');
            $aData = $aData[0];
        }

        $iTaskId = ArrayHelper::getValue($aData, 'taskId', 0);

        /** Запуск задачи */
        $aRes = Task::runTask($aConfig, $iTaskId, $bRunTaskByClassName);

        if (in_array($aRes['status'], [Task::stFrozen, Task::stWait])) {
            /* Ставим на повторный запуск */
            $this->addJSListener($sAction, $sAction);
            $this->fireJSEvent($sAction, ['taskId' => $aRes['id']]);
        }

        return $aRes;
    }
}
