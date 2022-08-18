<?php

namespace skewer\build\Tool\Schedule;

use skewer\base\queue\Api as QueueApi;
use skewer\base\queue\ar\Schedule;
use skewer\base\queue\Task;
use skewer\base\ui\ARSaveException;
use skewer\build\Tool;

/**
 * Интерфейс для работы с планировщиком задач.
 */
class Module extends Tool\LeftList\ModulePrototype
{
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
        $this->setPanelName(\Yii::t('schedule', 'taskList'));

        $items = Schedule::find()
            ->orderBy('title')
            ->asArray()
            ->all();

        foreach ($items as &$item) { // декорируем значения подписями
            foreach (['c_min', 'c_hour', 'c_day', 'c_month', 'c_dow'] as $key) {
                if ($item[$key] === null) {
                    $item[$key] = '*';
                }
            }

            $item['status'] = \Yii::t('schedule', Api::getStatusArray()[$item['status']]);
            $item['priority'] = \Yii::t('schedule', Api::getPriorityArray()[$item['priority']]);
            $item['target_area'] = \Yii::t('schedule', Api::getTargetArray()[$item['target_area']]);
            $item['resource_use'] = \Yii::t('schedule', Api::getResourceArray()[$item['resource_use']]);
        }

        $this->render(new Tool\Schedule\view\Index([
            'aItems' => $items,
        ]));
    }

    /**
     * Отображение формы.
     */
    protected function actionShow()
    {
        // взять id ( 0 - добавление, иначе сохранение )
        $iItemId = (int) $this->getInDataVal('id');

        // заголовок
        $this->setPanelName($iItemId ? \Yii::t('schedule', 'edit') : \Yii::t('schedule', 'add'));

        // элемент
        if ($iItemId) {
            $aItem = Schedule::findOne(['id' => $iItemId]);
            // если нет требуемой записи
            if (!$aItem) {
                throw new \Exception(\Yii::t('schedule', 'selectError'));
            }
        } else {
            $aItem = new Schedule();
            $aItem->setDefaultValues();
        }

        $this->render(new Tool\Schedule\view\Show([
            'aPriorityArray' => Api::getPriorityArray(),
            'aResourceArray' => Api::getResourceArray(),
            'aTargetArray' => Api::getTargetArray(),
            'aStatusArray' => Api::getStatusArray(),
            'iItemId' => $iItemId,
            'aItem' => $aItem,
        ]));
    }

    /**
     * Принудительно запускает указанную задачу.
     *
     * @throws ARSaveException
     */
    protected function actionTryTask()
    {
        $aData = $this->get('data');

        // есть данные
        if ($aData) {
            // сохранить
            if (($aData['id'] == 0) or (!$scheduleItem = Schedule::findOne($aData['id']))) {
                $scheduleItem = new Schedule();
                unset($aData['id']);
            }

            $scheduleItem->setAttributes($aData);

            // сохранение с валидацией
            if (!$scheduleItem->save()) {
                throw new ARSaveException($scheduleItem);
            }
            // добавление записи в лог
            if (isset($aData['id']) && $aData['id']) {
                $this->addMessage(\Yii::t('schedule', 'updateOk'));
                $this->addModuleNoticeReport(\Yii::t('schedule', 'editTabName'), $aData);
            } else {
                unset($aData['id']);
                $this->addMessage(\Yii::t('schedule', 'addOk'));
                $this->addModuleNoticeReport(\Yii::t('schedule', 'addTabName'), $aData);
            }

            $aTaskData = [
                'title' => $scheduleItem->getAttribute('title'),
                'priority' => $scheduleItem->getAttribute('priority'),
                'resource_use' => $scheduleItem->getAttribute('resource_use'),
                'target_area' => $scheduleItem->getAttribute('target_area'),
            ];

            $command = json_decode($scheduleItem->getAttribute('command'), true);

            //Если указан метод - запустим универсальную задачу на выполнение одного метода
            if (isset($command['method'])) {
                $aTaskData['class'] = '\skewer\base\queue\MethodTask';
                $aTaskData['parameters'] = [
                    'parameters' => $command['parameters'],
                    'class' => $command['class'],
                    'method' => $command['method'],
                ];
            } else {
                $aTaskData['class'] = $command['class'];
                $aTaskData['parameters'] = $command['parameters'];
            }

            $iTaskId = QueueApi::addTask(
                $aTaskData
            );

            //Вручную дернем cron
            file_get_contents(WEBPROTOCOL . WEBROOTPATH . 'cron');

            $aTask = \skewer\base\queue\ar\Task::find()
                ->where('id', $iTaskId)
                ->asArray()
                ->getOne();

            if ($aTask['status'] == Task::stClose) {
                $this->addMessage(\Yii::t('schedule', 'success_execute'), \Yii::t('schedule', 'task_executed'));
            } else {
                $this->addError(\Yii::t('schedule', 'error_execute'), \Yii::t('schedule', 'task_executed_error'));
            }
        }
    }

    /**
     * Сохранение.
     */
    protected function actionSave()
    {
        // запросить данные
        $aData = $this->get('data');

        // есть данные
        if ($aData) {
            if (($aData['id'] == 0) or (!$scheduleItem = Schedule::findOne($aData['id']))) {
                $scheduleItem = new Schedule();
                unset($aData['id']);
            }

            $scheduleItem->setAttributes($aData);

            // сохранение с валидацией
            if (!$scheduleItem->save()) {
                throw new ARSaveException($scheduleItem);
            }
            // добавление записи в лог
            if (isset($aData['id']) && $aData['id']) {
                $this->addMessage(\Yii::t('schedule', 'updateOk'));
                $this->addModuleNoticeReport(\Yii::t('schedule', 'editTabName'), $aData);
            } else {
                unset($aData['id']);
                $this->addMessage(\Yii::t('schedule', 'addOk'));
                $this->addModuleNoticeReport(\Yii::t('schedule', 'addTabName'), $aData);
            }
        }

        // вывод списка
        $this->actionInit();
    }

    /**
     * Удаление.
     */
    protected function actionDelete()
    {
        // запросить данные
        $aData = $this->get('data');

        // id записи
        $iItemId = (is_array($aData) and isset($aData['id'])) ? (int) $aData['id'] : 0;

        // удаление
        Schedule::deleteAll(['id' => $iItemId]);

        // добавление записи в лог
        $this->addMessage('Запись удалена');
        $this->addModuleNoticeReport('Удаление записи планировшика', $aData);

        // вывод списка
        $this->actionInit();
    }
}
