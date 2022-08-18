<?php

namespace skewer\build\Tool\TasksManager;

use skewer\base\queue;
use skewer\build\Tool;

/**
 * Интерфейс для работы с планировщиком задач.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    protected function preExecute()
    {
    }

    /**
     * Первичное состояние.
     */
    protected function actionInit()
    {
        // вывод списка
        $this->actionList();
    }

    /**
     * Список пользователей.
     */
    protected function actionList()
    {
        // установка заголовка
        $this->setPanelName(\Yii::t('TasksManager', 'tasks'));

        $aItems = Api::getListItems();

        $this->render(new Tool\TasksManager\view\Index([
            'aItems' => $aItems,
        ]));
    }

    /**
     * Удалеение х задач из списка.
     */
    protected function actionClear()
    {
        queue\Manager::clear();
        $this->actionList();
    }
}
