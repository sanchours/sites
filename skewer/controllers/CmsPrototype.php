<?php

namespace skewer\controllers;

use Exception;
use skewer\base\log\Logger;
use skewer\base\site_module;
use skewer\base\site_module\Context;
use skewer\components\auth\Auth;
use skewer\components\auth\CurrentAdmin;
use skewer\helpers\Linker;

/**
 * Прототип контроллера для системы администрирования.
 */
abstract class CmsPrototype extends Prototype
{
    const labelOut = 'out';

    /**
     * Отдает имя ключа для сессионного хранилища.
     *
     * @return string
     */
    abstract protected function getSessionKeyName();

    /**
     * Возвращает имя модуля основного слоя.
     *
     * @return string
     */
    abstract public function getLayoutModuleName();

    /**
     * Возвращает имя модуля авторизации.
     *
     * @return string
     */
    abstract public function getAuthModuleName();

    /**
     * Возвращает имя первично инициализируемого модуля.
     *
     * @return string
     */
    abstract public function getFrameModuleName();

    /**
     * Возвращает имя первично инициализируемого модуля при отсутствии авторизации.
     *
     * @return string
     */
    abstract public function getFrameAuthModuleName();

    /**
     * Отдает базовый url для сервиса.
     *
     * @return string
     */
    abstract public function getBaseUrl();

    /**
     * Запускает на выполнение корневой процесс, выводит результат работы дерева процессов.
     *
     * @static
     *
     * @throws Exception
     *
     * @return bool|string
     */
    public function runApplication()
    {
        /** @var $oRootProcess site_module\Process */
        $oRootProcess = null;

        // если запрос для админ интерфейса
        if (site_module\Request::isCmsRequest()) {
            try {
                $oProcessSession = new site_module\ProcessSession($this->getSessionKeyName());

                $sSessionId = site_module\Request::getSessionId();

                if (!$sSessionId) {
                    $sSessionId = $oProcessSession->createSession();
                    site_module\Request::setSessionId($sSessionId);
                }

                if ($oProcessSession->isExists($sSessionId)) {
                    $oRootProcess = $oProcessSession->load($sSessionId);
                    \Yii::$app->jsonResponse->addSessionId($sSessionId);

                    if ($oRootProcess instanceof site_module\Process) {
                        \Yii::$app->language = $oRootProcess->getData('language');

                        // если не авторизован и корневой проуесс не авторизация
                        if (!CurrentAdmin::isLoggedIn()) {
                            $sAuthModuleName = $this->getAuthModuleName();
                            if ($oRootProcess->getModuleClass() !== $sAuthModuleName) {
                                // перезагрузить интерфейс
                                \Yii::$app->jsonResponse->addJSONResponseRootValue('reload', true);
                            }
                        }

                    }
                } else {
                    \Yii::$app->jsonResponse->addJSONResponseRootValue('reload', true);
                }

                $iLoopCnt = 0;
                $iStatus = 0;

                // выполнять процесс пока он возвращает psExit
                do {
                    if (++$iLoopCnt > 20) {
                        throw new Exception('loop error: infinit reset status');
                    }
                    if ($iStatus == psReset) {
                        \Yii::$app->processList->removeProcess(self::labelOut);
                        $oRootProcess = null;
                    }

                    // если процесс уже был создан в предыдущих запросах
                    if ($oRootProcess instanceof site_module\Process) {
                        \Yii::$app->processList->setProcessToLabel(self::labelOut, $oRootProcess);
                        \Yii::$app->processList->recoverProcessPaths();

                        // разбираем JSON пакет и инитим процессы на запуск
                        if ($aJSONPackage = \Yii::$app->getRequest()->post('data', false)) {
                            foreach ($aJSONPackage as $sPath => $aPacket) {
                                if (($oProcess = \Yii::$app->processList->getProcess($sPath, psRendered)) instanceof site_module\Process) {
                                    $oProcess->setStatus(psNew);
                                }
                            }
                        }
                    }

                    // корневого процесса еще нет - создаем его
                    else {
                        // Проверка имеет ли пользователь сответствующие права
                        if (CurrentAdmin::isLoggedIn()) {
                            // добавление корневого процесса
                            $oRootProcess = \Yii::$app->processList->addProcess(new Context(self::labelOut, $this->getLayoutModuleName(), ctModule, []));
                        } else {
                            $oRootProcess = \Yii::$app->processList->addProcess(new Context(self::labelOut, $this->getAuthModuleName(), ctModule, ['viewMode' => 'form']));
                        }
                    }

                    // выполнить процесс
                    $iStatus = \Yii::$app->processList->executeProcessList();
                } while ($iStatus == psExit or $iStatus == psReset);

                // отрендерить результат
                $oRootProcess->render();
                $oRootProcess->setData('language', \Yii::$app->language);
                $oProcessSession->save($oRootProcess, $sSessionId);

                // добавить в ответ результат работы - success
                \Yii::$app->jsonResponse->addResponseStatus('Ok', true);

                // дополнительные файлы
                $aAddJSFiles = Linker::getJsFiles();
                $aAddCSSFiles = Linker::getCssFiles();
                if ($aAddJSFiles or $aAddCSSFiles) {
                    \Yii::$app->jsonResponse->addJSONResponseRootValue('addFiles', [
                        'js' => $aAddJSFiles,
                        'css' => $aAddCSSFiles,
                    ]);
                }
            } catch (\Exception $e) {
                Logger::dumpException($e);

                \Yii::error((string) $e);

                \Yii::$app->response->setStatusCode(500);

                \Yii::$app->jsonResponse->addResponseStatus($e->getMessage(), false);
            }

            // собрать ответ в формате JSON
            $sOut = json_encode(\Yii::$app->jsonResponse->getJSONResponse(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        }

        // не JSON - первичный вызов
        else {
            try {
                \skewer\components\redirect\Api::execute();
            } catch (\yii\base\Exception $e) {
                Logger::dumpException($e);
            }

            if (isset($_GET['token']) && ($sToken = $_GET['token']) && (!isset($_GET['cmd']))) {
                Auth::authUserByToken($sToken);
                \Yii::$app->getResponse()->redirect($this->getBaseUrl(), 301)->send();
            }

            // если не залогинен и есть спец модуль для авторизации
            if (!CurrentAdmin::isLoggedIn() and $this->getFrameAuthModuleName()) {
                $sFrameModuleName = $this->getFrameAuthModuleName();
            } else {
                // иначе загружаем стандартный
                $sFrameModuleName = $this->getFrameModuleName();
            }

            // загружаем основную обвязку страницы
            $oRootProcess = \Yii::$app->processList->addProcess(new Context(self::labelOut, $sFrameModuleName, ctModule));

            // выполняем
            \Yii::$app->processList->executeProcessList();

            // рендерим
            $oRootProcess->render();

            // собираем ответ
            $sOut = $oRootProcess->getOuterText();
        }

        return $sOut;
    }
}
