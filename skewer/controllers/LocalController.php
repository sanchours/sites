<?php

namespace skewer\controllers;

use skewer\base\site\Layer;
use skewer\base\site_module;
use skewer\components\auth\CurrentAdmin;

class LocalController extends Prototype
{
    public function actionIndex()
    {
        // проверка на авторизацию
        if (!CurrentAdmin::isLoggedIn()) {
            \Yii::$app->getResponse()->setStatusCode(401);

            // при отсутствии авторизации перегружает родительское
            //  окно, а текущее закрывает
            echo '<script type="text/javascript">
                if ( window.opener ) {
                    window.opener.location.reload();
                    window.close();
                }
            </script>not authorized';
        }

        // запрос контроллера
        if (($sController = site_module\Request::getStr('ctrl')) === null) {
            return 'controller not declared';
        }

        /**
         * собираем имя модуля + Local
         * если есть - выполняем
         */
        $sClassName = site_module\Module::getClass($sController, Layer::TOOL, 'Local');

        if (!class_exists($sClassName)) {
            return 'Access denied';
        }

        // добавление процесса
        \Yii::$app->processList->addProcess(new site_module\Context('out', $sClassName, ctModule));
        // выполнение процесса
        \Yii::$app->processList->executeProcessList();

        return '';
    }
}
