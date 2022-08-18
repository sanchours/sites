<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 19.01.2017
 * Time: 10:57.
 */

namespace skewer\build\Tool\Logger\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $aUsersLogin;
    public $aModules;
    public $aEventLevels;
    public $aLogTypes;

    public $mLoginFilter;
    public $mModuleFilter;
    public $mLevelFilter;
    public $mLogFilter;
    public $mDateFilter1;
    public $mDateFilter2;

    public $bIsSystemMode;

    public $aItems;
    public $iOnPage;
    public $iPage;
    public $iCount;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->fieldString('login', \Yii::t('Logger', 'login'))
            ->fieldString('event_time', \Yii::t('Logger', 'field_event_time'))
            ->fieldString('title', \Yii::t('Logger', 'field_title'), ['listColumns' => ['flex' => 2]])
            ->fieldString('module_title', \Yii::t('Logger', 'field_module_title'), ['listColumns' => ['width' => 150]])
            ->fieldString('event_title', \Yii::t('Logger', 'field_event_title'))
            ->fieldString('log_title', \Yii::t('Logger', 'field_log_title'), ['listColumns' => ['width' => 150]])
            ->fieldString('ip', \Yii::t('Logger', 'field_ip'))
            // фильтр по пользователям
            ->filterSelect('login', $this->aUsersLogin, $this->mLoginFilter, \Yii::t('logger', 'user'))

            // фильтр по модулям
            ->filterSelect('module', $this->aModules, $this->mModuleFilter, \Yii::t('logger', 'module'))

            // фильтр по уровню событий
            ->filterSelect('event_type', $this->aEventLevels, $this->mLevelFilter, \Yii::t('logger', 'event_type'))

            // фильтр по уровню событий
            ->filterSelect('log_type', $this->aLogTypes, $this->mLogFilter, \Yii::t('logger', 'log_type'))

            // фильтр по дате
            ->filterDate('date', [$this->mDateFilter1, $this->mDateFilter2], \Yii::t('logger', 'date'));

        // логи чистить может только sys
        if ($this->bIsSystemMode) {
            // кнопка очистки логов
            $this->_list->filterButton('clearLog', \Yii::t('logger', 'deleteLogs'), \Yii::t('logger', 'deleteLogsText'));
        }
        $this->_list->buttonRow('showForm', \Yii::t('logger', 'detailPage'), 'icon-edit', 'edit_form')
            ->setValue(
                $this->aItems,
                $this->iOnPage,
                $this->iPage,
                $this->iCount
            );
    }
}
