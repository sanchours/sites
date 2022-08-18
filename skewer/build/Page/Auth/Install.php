<?php

namespace skewer\build\Page\Auth;

use skewer\base\queue\ar\Schedule;
use skewer\components\auth\DeleteExpiredTask;
use skewer\components\config\InstallPrototype;
use skewer\components\forms\service\FormService;

/**
 * @author kolesnikov, $Author: sapozhkov $
 *
 * @version $Revision: $
 * @date $Date: $
 */
class Install extends InstallPrototype
{
    /** @var FormService $_formService */
    private $_formService;

    public function init()
    {
        $this->_formService = new FormService();

        return true;
    }

    // func

    /**
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \skewer\components\config\UpdateException
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function install()
    {
        $iNewPageSection = \Yii::$app->sections->tplNew();

        $this->addParameter($iNewPageSection, 'object', 'Auth', '', 'auth', '');
        $this->addParameter($iNewPageSection, 'layout', 'left,right', '', 'auth', '');
        $this->addParameter($iNewPageSection, 'mini_auth', '1', '', 'auth', '');

        $this->addParameter($iNewPageSection, 'object', 'Auth', '', 'authHead', '');
        $this->addParameter($iNewPageSection, 'layout', 'head', '', 'authHead', '');
        $this->addParameter($iNewPageSection, 'mini_auth', '1', '', 'authHead', '');
        $this->addParameter($iNewPageSection, 'head', '1', '', 'authHead', '');

        if (!$this->_formService->hasFormWithSlug(RegUserEntity::tableName())) {
            RegUserEntity::createTable();
        }

        if (!$this->_formService->hasFormWithSlug(AuthEntity::tableName())) {
            AuthEntity::createTable();
        }

        if (!$this->_formService->hasFormWithSlug(RecoverEntity::tableName())) {
            RecoverEntity::createTable();
        }

        if (!$this->_formService->hasFormWithSlug(NewPassEntity::tableName())) {
            NewPassEntity::createTable();
        }

        if (!Schedule::findOne(['name' => DeleteExpiredTask::SCHEDULE_TASK_NAME])) {
            $command = [
                'class' => 'skewer\components\auth\DeleteExpiredTask',
                'parameters' => [],
            ];

            $data = [
                'title' => 'Удаление неактивированных аккаунтов',
                'name' => DeleteExpiredTask::SCHEDULE_TASK_NAME,
                'command' => json_encode($command),
                'priority' => '1',
                'resource_use' => '7',
                'target_area' => '3',
                'status' => '1',
                'c_min' => '0',
                'c_hour' => '0',
                'c_day' => null,
                'c_month' => null,
                'c_dow' => null,
            ];

            $scheduleTask = new Schedule($data);
            $scheduleTask->save();
        }

        return true;
    }

    // func

    public function uninstall()
    {
        $iNewPageSection = \Yii::$app->sections->tplNew();

        $this->removeParameter($iNewPageSection, 'object', 'auth');
        $this->removeParameter($iNewPageSection, 'layout', 'auth');
        $this->removeParameter($iNewPageSection, 'mini_auth', 'auth');
        $this->removeParameter($iNewPageSection, 'head', 'auth');

        $this->removeParameter($iNewPageSection, 'object', 'authHead');
        $this->removeParameter($iNewPageSection, 'layout', 'authHead');
        $this->removeParameter($iNewPageSection, 'mini_auth', 'authHead');
        $this->removeParameter($iNewPageSection, 'head', 'authHead');

        $schedulerTask = Schedule::findOne(['name' => DeleteExpiredTask::SCHEDULE_TASK_NAME]);
        if ($schedulerTask) {
            $schedulerTask->delete();
        }

        return true;
    }

    // func
}// class
