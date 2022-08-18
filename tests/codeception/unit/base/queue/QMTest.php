<?php

namespace unit\base\queue;

use skewer\base\orm\Query;
use skewer\base\queue\Api;
use skewer\base\queue\ar;
use skewer\base\queue\Manager;
use skewer\base\queue\Task;
use skewer\base\SysVar;

class QMTest extends \Codeception\Test\Unit
{
    const className = 'unit\base\queue\TestTask';

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        //Тестовая задача
        require_once __DIR__ . '/TestTask.php';
        require_once __DIR__ . '/TimeTask.php';

        defined('TestTask') or define('TestTask', 1);

        parent::__construct($name, $data, $dataName);
    }

    /**
     * Тест на создание задач.
     *
     * @covers \skewer\base\queue\Api::addTask
     * @covers \skewer\base\queue\Api::createTask
     */
    public function testCreateTask()
    {
        /* Тесты на пустые данные */
        $this->assertFalse(Api::addTask([]));
        $this->assertFalse(Api::addTask(['title' => 'test']));
        $this->assertFalse(Api::addTask(['class' => static::className]));

        $this->assertTrue(Api::addTask(['title' => 'test', 'class' => static::className]) > 0);

        /** Тесты на повтор */
        $aTask = ['title' => 'test', 'class' => static::className];

        Api::addTask($aTask);
        $this->assertFalse(Api::addTask($aTask));

        $aTask = ['title' => 'test', 'class' => static::className, 'parameters' => ['param' => 1]];

        Api::addTask($aTask);
        $this->assertFalse(Api::addTask($aTask));

        $aTask['parameters']['param'] = 2;
        $this->assertTrue(Api::addTask($aTask) > 0);
        $this->assertFalse(Api::addTask($aTask));
    }

    private function clearTask()
    {
        ar\Task::delete()->get();
    }

    /**
     * Тесты на получение первой задачи.
     *
     * @covers \skewer\base\queue\Api::getFirstTask
     * @covers \skewer\base\queue\Api::createTask
     */
    public function testGetFirstTask()
    {
        $this->clearTask();
        $this->assertFalse(Api::getFirstTask());

        /* Приоритет */
        Api::addTask(['title' => 'test', 'class' => static::className, 'priority' => Task::priorityLow, 'parameters' => ['par' => 1]]);

        $this->assertTrue(Api::getFirstTask() != false);

        /* Задача уже взята, повторно не взять! */
        $this->assertFalse(Api::getFirstTask());

        $this->clearTask();

        Api::addTask(['title' => 'test', 'class' => static::className, 'priority' => Task::priorityLow, 'parameters' => ['par' => 1]]);
        $idNorm = Api::addTask(['title' => 'test', 'class' => static::className, 'priority' => Task::priorityNormal, 'parameters' => ['par' => 2]]);

        $this->assertEquals($idNorm, Api::getFirstTask()->getId());

        $idHigh = Api::addTask(['title' => 'test', 'class' => static::className, 'priority' => Task::priorityHigh, 'parameters' => ['par' => 3]]);
        $this->assertEquals($idHigh, Api::getFirstTask()->getId());

        $this->clearTask();

        Api::addTask(['title' => 'test', 'class' => static::className, 'priority' => Task::priorityLow, 'parameters' => ['par' => 1]]);
        Api::addTask(['title' => 'test', 'class' => static::className, 'priority' => Task::priorityNormal, 'parameters' => ['par' => 2]]);
        $idHigh = Api::addTask(['title' => 'test', 'class' => static::className, 'priority' => Task::priorityHigh, 'parameters' => ['par' => 3]]);
        Api::addTask(['title' => 'test', 'class' => static::className, 'priority' => Task::priorityLow, 'parameters' => ['par' => 4]]);
        Api::addTask(['title' => 'test', 'class' => static::className, 'priority' => Task::priorityNormal, 'parameters' => ['par' => 5]]);

        $this->assertEquals($idHigh, Api::getFirstTask()->getId());

        /* С родителем */
        $this->clearTask();

        Api::addTask(['title' => 'test', 'class' => static::className, 'priority' => Task::priorityLow, 'parameters' => ['par' => 1]]);
        Api::addTask(['title' => 'test', 'class' => static::className, 'priority' => Task::priorityNormal, 'parameters' => ['par' => 2]]);
        $idHigh = Api::addTask(['title' => 'test', 'class' => static::className, 'priority' => Task::priorityHigh, 'parameters' => ['par' => 3]]);
        Api::addTask(['title' => 'test', 'class' => static::className, 'priority' => Task::priorityLow, 'parameters' => ['par' => 4]]);
        Api::addTask(['title' => 'test', 'class' => static::className, 'priority' => Task::priorityNormal, 'parameters' => ['par' => 5]]);

        $idCr = Api::addTask(['title' => 'test', 'class' => static::className, 'priority' => Task::priorityCritical, 'parent' => 123, 'parameters' => ['par' => 56]]);
        $id = Api::getFirstTask()->getId();
        $this->assertEquals($idHigh, $id);
        $this->assertNotEquals($idCr, $id);
    }

    /**
     * Тесты на получение задачи.
     *
     * @covers \skewer\base\queue\Api::getTaskById
     * @covers \skewer\base\queue\Api::getTaskByClassName
     * @covers \skewer\base\queue\Api::createTask
     */
    public function testGetTask()
    {
        $this->clearTask();
        $id = Api::addTask(['title' => 'testAdd', 'class' => static::className, 'parameters' => ['param' => '123']]);

        $oTask = Api::getTaskById($id);

        $this->assertTrue($oTask instanceof Task);
        $this->assertEquals(get_class($oTask), static::className);
        $this->assertEquals($oTask->getId(), $id);

        $this->clearTask();

        $id = Api::addTask(['title' => 'testAdd', 'class' => static::className, 'parameters' => ['param' => '123']]);
        Api::addTask(['title' => 'testAdd', 'class' => '\\skewer\\base\\queue\\MethodTask', 'priority' => Task::priorityCritical]);

        $oTask = Api::getTaskByClassName(static::className);
        $this->assertTrue($oTask instanceof Task);
        $this->assertEquals(get_class($oTask), static::className);
        $this->assertEquals($oTask->getId(), $id);

        $this->clearTask();
        $this->assertFalse(Api::getTaskById(111111));
        $this->assertFalse(Api::getTaskByClassName('NotFoundClass'));
    }

    /**
     * Тесты на под задачи.
     *
     * @covers \skewer\base\queue\Api::getChildCount
     * @covers \skewer\base\queue\Api::getFirstTask4Parent
     */
    public function testChildTask()
    {
        $this->clearTask();
        $id = Api::addTask(['title' => 'testAdd', 'class' => static::className, 'parameters' => ['param' => 1]]);
        Api::addTask(['title' => 'testAdd', 'class' => static::className, 'parameters' => ['param' => 2], 'parent' => $id]);
        $iPr = Api::addTask(['title' => 'testAdd', 'class' => static::className, 'parameters' => ['param' => 3], 'parent' => $id, 'priority' => Task::priorityCritical]);
        Api::addTask(['title' => 'testAdd', 'class' => static::className, 'parameters' => ['param' => 4], 'parent' => $id]);

        $iCount = Api::getChildCount(Api::getTaskById($id));
        $this->assertEquals($iCount, 3);

        $oTask = Api::getFirstTask4Parent($id);
        $this->assertEquals($oTask->getId(), $iPr);

        $this->clearTask();
        $oTask = Api::getFirstTask4Parent($id);
        $this->assertFalse($oTask);
    }

    /**
     * Тесты на смену статусов.
     *
     * @covers \skewer\base\queue\Api::updateStatusTask
     * @covers \skewer\base\queue\Task::setStatus
     */
    public function testUpdStatus()
    {
        $this->clearTask();

        $id = Api::addTask(['title' => 'testAdd', 'class' => static::className, 'parameters' => ['param' => 1]]);

        $getStatusTask = static function ($id) {
            $oTaskRow = Api::getTaskRowById($id);

            return $oTaskRow->status;
        };

        $this->assertEquals($getStatusTask($id), Task::stNew);

        $oTask = Api::getTaskById($id);

        // после выборки задача инициализирована
        $this->assertEquals($oTask->getStatus(), Task::stInit);
        $this->assertEquals($getStatusTask($id), Task::stInit);

        $oTask->setStatus(Task::stInterapt);
        $this->assertEquals($oTask->getStatus(), Task::stInterapt);
        $this->assertEquals($getStatusTask($id), Task::stInterapt);

        $oTask->setStatus(Task::stComplete);
        $this->assertEquals($oTask->getStatus(), Task::stComplete);
        $this->assertEquals($getStatusTask($id), Task::stComplete);

        $oTask->setStatus(Task::stClose);
        $this->assertEquals($oTask->getStatus(), Task::stClose);
        $this->assertEquals($getStatusTask($id), Task::stClose);

        /* Статус меняем, но не сохраняем в бд */
        $oTask->setStatus(Task::stError, false);
        $this->assertEquals($oTask->getStatus(), Task::stError);
        $this->assertEquals($getStatusTask($id), Task::stClose);
    }

    /**
     * Тесты на смену статусов.
     *
     * @covers \skewer\base\queue\Api::updateStatusTask
     */
    public function testErrorUpdStatus()
    {
        $this->expectException(\Exception::class);

        $this->clearTask();
        $id = Api::addTask(['title' => 'testAdd', 'class' => static::className, 'parameters' => ['param' => 1]]);
        $oTask = Api::getTaskById($id);
        $this->clearTask();

        $oTask->setStatus(Task::stError);
    }

    /**
     * Тесты на сохранение задачи.
     *
     * @covers \skewer\base\queue\Api::saveTask
     */
    public function testSaveTask()
    {
        $this->clearTask();

        $id = Api::addTask(['title' => 'testAdd', 'class' => static::className, 'parameters' => ['param' => 1]]);

        $oTask = Api::getTaskById($id);

        $oTask->setStatus(Task::stProcess, false);
        Api::saveTask($oTask, ['param' => 2, 'param2' => 4]);

        $oTaskRow = Api::getTaskRowById($oTask->getId());

        $this->assertEquals($oTaskRow->status, Task::stProcess);
        $this->assertEquals($oTaskRow->parameters, json_encode(['param' => 2, 'param2' => 4]));
    }

    /**
     * Тесты на мютекс
     *
     * @covers \skewer\base\queue\Api::holdItem
     * @covers \skewer\base\queue\Api::unholdItem
     */
    public function testHoldItem()
    {
        $this->clearTask();
        $id = Api::addTask(['title' => 'testAdd', 'class' => static::className, 'parameters' => ['param' => 1]]);

        $getMutexTask = static function ($id) {
            $oTaskRow = Api::getTaskRowById($id);

            return $oTaskRow->mutex;
        };

        $this->assertEquals($getMutexTask($id), 0);

        $iHold = Api::holdItem($id);
        $this->assertEquals($iHold, 1);
        $this->assertEquals($getMutexTask($id), 1);

        /** Повторно */
        $iHold = Api::holdItem($id);
        $this->assertEquals($iHold, 0);
        $this->assertEquals($getMutexTask($id), 1);

        Api::unholdItem($id);
        $this->assertEquals($getMutexTask($id), 0);
    }

    /**
     * Тест на очистку устаревших данных.
     *
     * @covers \skewer\base\queue\Api::collectGarbage
     */
    public function testGC()
    {
        $getCount = static function () {
            return ar\Task::find()->getCount();
        };

        /* Удаление старых закрытых */
        $this->clearTask();

        /* Сохраняем так, так как в TaskRow дата сама выставляется при сохранении */
        Query::InsertInto('task')
            ->set('class', 'test')
            ->set('status', Task::stClose)
            ->set('upd_time', date('Y-m-d H:i:s', strtotime('-16 hour')))
            ->get();

        Query::InsertInto('task')
            ->set('class', 'test')
            ->set('status', Task::stClose)
            ->set('upd_time', date('Y-m-d H:i:s', strtotime('-10 hour')))
            ->get();

        Query::InsertInto('task')
            ->set('class', 'test')
            ->set('status', Task::stError)
            ->set('upd_time', date('Y-m-d H:i:s', strtotime('-13 hour')))
            ->get();

        $this->assertEquals($getCount(), 3);

        Api::collectGarbage();
        $this->assertEquals($getCount(), 2);

        /* Удаление старых */
        $this->clearTask();

        Query::InsertInto('task')
            ->set('class', 'test')
            ->set('status', Task::stTimeout)
            ->set('upd_time', date('Y-m-d H:i:s', strtotime('-13 day')))
            ->get();

        Query::InsertInto('task')
            ->set('class', 'test')
            ->set('status', Task::stTimeout)
            ->set('upd_time', date('Y-m-d H:i:s', strtotime('-3 day')))
            ->get();

        Query::InsertInto('task')
            ->set('class', 'test')
            ->set('status', Task::stError)
            ->set('upd_time', date('Y-m-d H:i:s', strtotime('-13 day')))
            ->get();

        $this->assertEquals($getCount(), 3);

        Api::collectGarbage();
        $this->assertEquals($getCount(), 1);

        /* Отметка повисших */
        $this->clearTask();

        $id = Query::InsertInto('task')
            ->set('class', 'test')
            ->set('mutex', 1)
            ->set('status', Task::stFrozen)
            ->set('upd_time', date('Y-m-d H:i:s', strtotime('-9 hour')))
            ->get(true);

        Api::collectGarbage();
        $this->assertEquals($getCount(), 1);
        /** @var ar\TaskRow $oRow */
        $oRow = ar\Task::find($id);
        $this->assertEquals($oRow->mutex, 0);
        $this->assertEquals($oRow->status, Task::stTimeout);

        /* Отметка недавних */
        $this->clearTask();

        $id = Query::InsertInto('task')
            ->set('class', 'test')
            ->set('status', Task::stInterapt)
            ->set('upd_time', date('Y-m-d H:i:s', strtotime('-5 hour')))
            ->get(true);

        Api::collectGarbage();
        $this->assertEquals($getCount(), 1);
        /** @var ar\TaskRow $oRow */
        $oRow = ar\Task::find($id);
        $this->assertEquals($oRow->mutex, 0);
        $this->assertEquals($oRow->status, Task::stTimeout);
    }

    /**
     * Тест цикла запуска задач.
     *
     * @covers \skewer\base\queue\Manager::execute
     * @covers \skewer\base\queue\Manager::executeTask
     * @covers \skewer\base\queue\Limiter::checkLimit
     */
    public function testExecute()
    {
        $getParam = static function () {
            return SysVar::get('TestTask.Param');
        };

        $getStatus = static function (Task $oTask) {
            $oTaskRow = Api::getTaskRowById($oTask->getId());

            return $oTaskRow->status;
        };

        $this->clearTask();

        $oMQ = Manager::getInstance();

        $id = Api::addTask(['title' => 'testAdd', 'class' => static::className, 'parameters' => ['param' => 0, 'id' => 0]]);

        $oTask = Api::getTaskById($id);
        $this->assertEquals($getStatus($oTask), Task::stInit);

        $oMQ->executeTask($oTask);

        /* Задача отработана, результат верный */
        $this->assertEquals($getStatus($oTask), Task::stClose);
        $this->assertEquals($getParam(), 3);

        $this->clearTask();

        /** Запуск нескольких */
        $id1 = Api::addTask(['title' => 'testAdd', 'class' => static::className, 'parameters' => ['param' => 0, 'id' => 1], 'priority' => Task::priorityCritical]);
        $id2 = Api::addTask(['title' => 'testAdd', 'class' => static::className, 'parameters' => ['param' => 0, 'id' => 2], 'priority' => Task::priorityNormal]);
        $id3 = Api::addTask(['title' => 'testAdd', 'class' => static::className, 'parameters' => ['param' => 0, 'id' => 3], 'priority' => Task::priorityHigh]);

        SysVar::set('TestTask.error', -1);

        $oMQ->execute();

        $oTask = Api::getTaskRowById($id1);
        $this->assertEquals($oTask->status, Task::stClose);
        $oTask = Api::getTaskRowById($id2);
        $this->assertEquals($oTask->status, Task::stError);
        $oTask = Api::getTaskRowById($id3);
        $this->assertEquals($oTask->status, Task::stClose);

        /* Последней должна отработать вторая задача! */
        $this->assertEquals(SysVar::get('TestTask.id'), 2);
        $this->assertEquals(SysVar::get('TestTask.error'), 2);
        $this->assertEquals($getParam(), 1);

        $this->clearTask();
        /** Подзадачи! */
        $id1 = Api::addTask(['title' => 'testAdd', 'class' => static::className, 'parameters' => ['param' => 0, 'id' => 6], 'priority' => Task::priorityCritical]);
        $id2 = Api::addTask(['title' => 'testAdd', 'class' => static::className, 'parameters' => ['param' => 0, 'id' => 4], 'priority' => Task::priorityNormal, 'parent' => $id1]);
        $id3 = Api::addTask(['title' => 'testAdd', 'class' => static::className, 'parameters' => ['param' => 0, 'id' => 5], 'priority' => Task::priorityHigh, 'parent' => $id1]);

        $oTask = Api::getTaskById($id1);

        $this->assertEquals(Api::getChildCount($oTask), 2);
        $oMQ->executeTask($oTask);

        $oTask1 = Api::getTaskRowById($id1);
        $this->assertEquals($oTask1->status, Task::stClose);
        $oTask2 = Api::getTaskRowById($id2);
        $this->assertEquals($oTask2->status, Task::stClose);
        $oTask3 = Api::getTaskRowById($id3);
        $this->assertEquals($oTask3->status, Task::stClose);

        $this->clearTask();
        /** Проверка времени */
        $id = Api::addTask(['title' => 'testAdd', 'class' => 'unit\base\queue\TimeTask', 'parameters' => ['param' => 0]]);

        $oTask = Api::getTaskById($id);

        $this->assertEquals($getStatus($oTask), Task::stInit);
        $oMQ->executeTask($oTask);

        /* Задача заморожена, результат верный */
        $this->assertEquals($getStatus($oTask), Task::stFrozen);
        $this->assertEquals($getParam(), 1);

        $oTask = Api::getTaskById($id);
        /* разморозка, задача готова к новому выполнению */
        $this->assertEquals($getStatus($oTask), Task::stInit);

        $oMQ->executeTask($oTask);
        /* Задача заморожена, результат верный, не хватило времени на отработку следующей итерации */
        $this->assertEquals($getStatus($oTask), Task::stFrozen);
        $this->assertEquals($getParam(), 1);
    }

    /**
     * Тест запуска задачи с неверным статусом
     *
     * @covers \skewer\base\queue\Manager::executeTask
     */
    public function testErrorExecute()
    {
        $this->expectException(\Exception::class);

        $this->clearTask();

        $oMQ = Manager::getInstance();

        $id = Api::addTask(['title' => 'testAdd', 'class' => static::className, 'parameters' => ['param' => 1]]);

        $oTask = Api::getTaskById($id);

        $oTask->setStatus(Task::stTimeout);

        $oMQ->executeTask($oTask);
    }
}
