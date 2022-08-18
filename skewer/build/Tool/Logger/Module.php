<?php

namespace skewer\build\Tool\Logger;

use skewer\base\log\models\Log;
use skewer\base\ui;
use skewer\build\Tool;
use skewer\components\auth\CurrentAdmin;
use skewer\components\auth\Users;

class Module extends Tool\LeftList\ModulePrototype
{
    protected $iOnPage = 20;
    protected $iPage = 0;

    // фильтр по модулям
    protected $mModuleFilter = false;
    // фильтр по пользователю
    protected $mLoginFilter = false;
    // фильтр по уровню событий
    protected $mLevelFilter = false;
    // фильтр по типу журнала
    protected $mLogFilter = false;
    // фильтры по датам
    protected $mDateFilter1 = false;
    protected $mDateFilter2 = false;

    protected function preExecute()
    {
        // номер страницы
        $this->iPage = $this->getInt('page');

        // запрос значений фильтров
        $this->mModuleFilter = $this->get('module', false);
        $this->mLoginFilter = $this->get('login', false);
        $this->mLevelFilter = $this->get('event_type', false);
        $this->mLogFilter = $this->get('log_type', false);
        $this->mDateFilter1 = $this->getDateFilter('date1');
        $this->mDateFilter2 = $this->getDateFilter('date2');
    }

    /**
     * Запрашивает значение фильтра по дате с валидацией.
     *
     * @param string $sName имя фильтра
     *
     * @return bool|string
     */
    protected function getDateFilter($sName)
    {
        // запрос значения
        $mVal = $this->getStr($sName, false);

        // проверка значения
        if (!$mVal) {
            return false;
        }

        // валидация
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $mVal)) {
            return $mVal;
        }

        return false;
    }

    public function actionInit()
    {
        $aFilter =
            [
                'limit' => $this->iOnPage ?
                    [
                        'start' => $this->iPage * $this->iOnPage,
                        'count' => $this->iOnPage,
                    ] : false,

                'order' => [
                        'field' => 'event_time',
                        'way' => 'DESC',
                    ],
            ];

        /*
         * Фильтры
         */

        // по названию модуля
        if (false !== $this->mModuleFilter) {
            $aFilter['module'] = $this->mModuleFilter;
        }

        // по логину
        if (false !== $this->mLoginFilter) {
            $aFilter['login'] = $this->mLoginFilter;
        }

        // по уровню доступа
        if (false !== $this->mLevelFilter) {
            $aFilter['event_type'] = $this->mLevelFilter;
        }

        // по типу журнала
        if (false !== $this->mLogFilter) {
            $aFilter['log_type'] = $this->mLogFilter;
        }

        // по дате
        if ($this->mDateFilter1 and $this->mDateFilter2) { // если заданы оба параметра
            $aFilter['event_time'] = [
                'sign' => 'BETWEEN',
                'value' => [$this->mDateFilter1, $this->mDateFilter2 . ' 23:59:59'],
            ];
        } elseif ($this->mDateFilter1) { // если только первый
            $aFilter['event_time'] = [
                'sign' => '>=',
                'value' => $this->mDateFilter1,
            ];
        } elseif ($this->mDateFilter2) { // если только второй
            $aFilter['event_time'] = [
                'sign' => '<=',
                'value' => $this->mDateFilter2 . ' 23:59:59',
            ];
        }

        // добавление набора данных
        $aItems = Api::getListItems($aFilter);

        $this->render(new Tool\Logger\view\Index([
            'aUsersLogin' => Api::getUsersLogin(),
            'aModules' => Api::getModules(),
            'aEventLevels' => Api::getEventLevels(),
            'aLogTypes' => Api::getLogTypes(),

            'mLoginFilter' => $this->mLoginFilter,
            'mModuleFilter' => $this->mModuleFilter,
            'mLevelFilter' => $this->mLevelFilter,
            'mLogFilter' => $this->mLogFilter,
            'mDateFilter1' => $this->mDateFilter1,
            'mDateFilter2' => $this->mDateFilter2,

            'bIsSystemMode' => CurrentAdmin::isSystemMode(),

            'aItems' => $aItems['items'],
            'iOnPage' => $this->iOnPage,
            'iPage' => $this->iPage,
            'iCount' => $aItems['count'],
        ]));
    }

    protected function actionClearLog()
    {
        // логи чистить может только sys
        if (CurrentAdmin::isSystemMode()) {
            Api::clearLog();
        }

        $this->actionInit();
    }

    /**
     * Отображение формы.
     */
    protected function actionShowForm()
    {
        $aData = $this->get('data');
        $iItemId = (is_array($aData) && isset($aData['id'])) ? (int) $aData['id'] : 0;

        $aItem = '';
        if ($oItem = Log::findOne($iItemId)) {
            $aItem = $oItem->getAttributes();

            $aModules = Api::getModules();

            // замена индексов на строки
            $aItem['event_type'] = Api::getLevelTitle($oItem->event_type);
            $aItem['log_type'] = Api::getTypeTitle($aItem['log_type']);
            $aItem['module'] = isset($aModules[$oItem->module]) ? $aModules[$oItem->module] : 'unknown';
            $aItem['title'] = \Yii::t('logger', $aItem['title']);

            // пользователь
            $aUser = Users::getUserData($oItem->initiator);
            if ($aUser) {
                $aItem['user'] = sprintf('%s (%s)', $aUser['login'], $aUser['name']);
            } else {
                $aItem['user'] = sprintf('unknown');
            }

            // форматирование данных описания
            $aDesc = json_decode($oItem->description);
            if (!json_last_error()) {
                $aItem['description'] = '<pre>' . print_r((array) $aDesc, true) . '</pre>';
            }
        }

        $this->render(new Tool\Logger\view\ShowForm([
            'bIsSystemMode' => CurrentAdmin::isSystemMode(),
            'bItemExists' => $oItem,
            'aItem' => $aItem,
        ]));
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
            'module' => $this->mModuleFilter,
            'event_type' => $this->mLevelFilter,
            'log_type' => $this->mLogFilter,
            'page' => $this->iPage,
            'date1' => $this->mDateFilter1,
            'date2' => $this->mDateFilter2,
        ]);
    }
}//class
