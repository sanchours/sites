<?php

namespace skewer\base\site;

use skewer\base\log\Logger;
use skewer\base\log\models\Log;
use skewer\base\queue\ar\Task;
use skewer\base\queue\ar\TaskRow;
use skewer\base\queue\Manager;
use skewer\base\queue\Task as BaseTask;
use skewer\base\SysVar;
use skewer\components\auth\Auth;
use skewer\components\auth\Users;
use skewer\components\config\UpdateHelper;
use skewer\components\gateway;
use skewer\components\modifications\Api;
use skewer\components\seo;
use Symfony\Component\Yaml\Yaml;

/**
 * Инструменты площадки, разрешенные к удвленному запуску
 * Вызываются из SMS. Прямого вызова в проекте ожет и не быть.
 */
class HostTools extends ServicePrototype
{
    /**
     * Возвращает статус площадки.
     *
     * @return string
     */
    public function getStatus()
    {
        $aOut['status'] = '200OK';
        $aOut['host'] = $_SERVER['HTTP_HOST'];
        $aOut['build_number'] = BUILDNUMBER;
        $aOut['build_name'] = BUILDNAME;

        return Yaml::dump($aOut);
    }

    // func

    /**
     * Устанавливает патч.
     *
     * @param string $sPatchFile путь к исполняемому файлу патча относительно корня директории обновлений
     *
     * @throws gateway\ExecuteException
     *
     * @return array|bool true/false/массив для отправки
     */
    public function installPatch($sPatchFile)
    {
        try {
            $oUpdateHelper = new UpdateHelper();
            $mResult = $oUpdateHelper->installPatch($sPatchFile, true);
        } catch (\Exception $e) {
            Logger::dumpException($e);

            /* что-то пошло не так. Файл обновления имеет неверный формат либо не достаточно входных параметров */
            throw new gateway\ExecuteException($e->getMessage());
        }

        return $mResult;
    }

    // func

    /**
     * Запуск после обновления.
     *
     * @param bool $bClear
     *
     * @return bool
     */
    public function UpdateComplete($bClear = true)
    {
        if ($bClear) {
            Log::deleteAll(); // чистим таблицу с логами

            \skewer\build\Tool\Subscribe\Api::clearPostingLog();
        }

        // upd
        \skewer\build\Tool\Redirect301\Api::makeHtaccessFile();
        seo\Service::updateSiteMap();
        seo\Service::updateRobotsTxt(\skewer\build\Tool\Domains\Api::getMainDomain());

        return true;
    }

    // func

    /**
     * @param $sTime
     *
     * @return bool
     */
    public function updTimeBackup($sTime)
    {
        \skewer\build\Tool\Backup\Api::updBackupTime($sTime);

        return true;
    }

    public function updRobotTxt($sDomain = false)
    {
        seo\Service::updateRobotsTxt($sDomain);

        return true;
    }

    public function syncDomain($aDomains)
    {
        \skewer\build\Tool\Domains\Api::syncDomains($aDomains);

        return true;
    }

    public function updCache()
    {
        \skewer\build\Tool\Utils\Api::dropCache();
    }

    public function updSitemap()
    {
        seo\Service::updateSiteMap();
    }

    public function clearQueue()
    {
        Manager::clear();
    }

    public function rebuildAll()
    {
        $this->updRobotTxt(\skewer\build\Tool\Domains\Api::getMainDomain());
        $this->updCache();
        $this->updSitemap();
        $this->updateGlobalIdForTasks();
    }

    /**
     * Обновляет global_id у задач.
     */
    public function updateGlobalIdForTasks()
    {
        $tasks = Task::find()->getAll();

        if (is_array($tasks) && count($tasks)) {
            foreach ($tasks as $task) {
                if ($task instanceof TaskRow) {
                    $taskData = $task->getData();
                    $taskData['global_id'] = '';

                    try {
                        $parameters = isset($taskData['parameters'])
                            ? json_encode($taskData['parameters'])
                            : '';
                        $priority = $taskData['priority']
                            ?? BaseTask::priorityLow;
                        $resource_use = $taskData['resource_use']
                            ?? BaseTask::weightLow;

                        $client = gateway\Api::createClient();

                        $command = json_encode(['class' => $taskData['class'], 'parameters' => $parameters]);
                        $params = [
                            $_SERVER['HTTP_HOST'],
                            $taskData['title'],
                            $command,
                            $priority,
                            $resource_use,
                        ];

                        $client->addMethod('HostTools', 'addTask', $params, static function ($globalId, $error) use ($taskData) {
                            if ($error) {
                                throw new \Exception($error);
                            }
                            $task = Task::find($taskData['id']);
                            if (!$task) {
                                throw new \Exception('Не найдена задача!');
                            }
                            $task->setData(['global_id' => $globalId]);
                            if (!$task->save()) {
                                throw new \Exception('Ошибка при сохранении глобального ID задачи');
                            }
                        });

                        if (!$client->doRequest()) {
                            throw new \Exception($client->getError());
                        }
                    } catch (\Throwable $e) {
                        Logger::dumpException($e);
                    }
                }
            }
        }
    }

    /**
     * Собирает с модулей данные о последней записи в таблице
     * Отдает дату последней модификации БД со стороны пользователя.
     *
     * @return int
     */
    public function getLastDBModification()
    {
        return Api::getMaxTime();
    }

    /**
     * Изменение пароля для политики admin.
     *
     * @param $sNewPass
     *
     * @return bool|int
     */
    public function replaceAdmPass($sNewPass)
    {
        $iLoginId = Users::getIdByLogin('admin');

        if (!$iLoginId) {
            return false;
        }

        $aSaveArr = [
            'id' => $iLoginId,
            'login' => 'admin',
            'pass' => Auth::buildPassword('admin', $sNewPass),
        ];

        $bRes = Users::updUser($aSaveArr);

        return $bRes;
    }

    /**
     * Отдает тэги сайта.
     *
     * @return string
     */
    public static function getTags()
    {
        $aTags = [];

        $aTags[] = BUILDNAME;
        $aTags[] = BUILDNUMBER;

        if (USECLUSTERBUILD) {
            $aTags[] = 'in_cluster';
        } else {
            $aTags[] = 'not_in_cluster';
        }

        $aTags[] = YII_ENV;

        $aTags[] = SysVar::get('site_type');

        return implode(';', $aTags);
    }

    /**
     * Разрещает либо запрещает работу процессоров в.
     *
     * @param bool $bEnabled
     *
     * @return bool Возвращает true в случае успешной смены состояния процессоров. В случае если данное состояние к
     * процессорам уже применено, то будет возвращено false
     */
    public static function enableProcessor($bEnabled = true)
    {
        if ((bool) SysVar::get('ProcessorEnable') === (bool) $bEnabled) {
            return false;
        }

        return SysVar::set('ProcessorEnable', $bEnabled);
    }

    // func

    /**
     * Запускает указанные методы класса HostTools
     * json_encode([.
     * [
     * 'method'=>'updRobotTxt',
     * 'params'=>[
     * 'param1',
     * 'param2'
     * ]
     * ],
     * ]).
     *
     * @param $sMethods
     */
    public static function executeMethods($sMethods)
    {
        $aMethods = json_decode($sMethods, true);

        $oHostTools = new HostTools();

        foreach ($aMethods as $aMethod) {
            /*Проверка существует ли метод*/
            if (!method_exists(__CLASS__, $aMethod['method'])) {
                continue;
            }

            $aParams = [];
            if (isset($aMethod['params'])) {
                foreach ($aMethod['params'] as $param) {
                    $aParams[] = $param;
                }
            }

            /*Запустим функцию с параметрами*/
            call_user_func_array([$oHostTools, $aMethod['method']], $aParams);
        }
    }
}// class
