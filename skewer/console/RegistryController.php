<?php

namespace app\skewer\console;

use skewer\components\config;
use skewer\components\config\installer;
use yii\helpers\Console;

/**
 * Класс для работы с реестром сайта (реестром установленных модулей)
 * Class RegistryController.
 */
class RegistryController extends Prototype
{
    public $defaultAction = 'modules';

    /**
     * Показывает список слоёв, для которых есть установленные модули.
     */
    public function actionLayers()
    {
        foreach (\Yii::$app->register->getLayerList() as $sLayer) {
            $this->stdout($sLayer);
            $this->br();
        }
    }

    /**
     * Отдает список умтановленных в слях модулей (без параметра - по всем).
     *
     * @param string $sLayer имя слоя если нужно ограничить
     */
    public function actionModules($sLayer = '')
    {
        $r = \Yii::$app->register;

        if ($sLayer) {
            if (!in_array($sLayer, $r->getLayerList())) {
                $this->stderr("No modules fo {$sLayer} layer");
                $this->br();

                return;
            }

            $sModuleList = [$sLayer];
        } else {
            $sModuleList = $r->getLayerList();
        }

        foreach ($sModuleList as $sLayer) {
            $this->stdout($sLayer, Console::BOLD, Console::UNDERLINE);
            $this->br();

            foreach ($r->getModuleList($sLayer) as $sModule) {
                $this->stdout(sprintf(
                    '  %s - %s',
                    $this->ansiFormat($sModule, Console::UNDERLINE),
                    $r->getModuleConfig($sModule, $sLayer)->getTitle()
                ));
                $this->br();
            }

            $this->br();
        }
    }

    /**
     * Отображает всю информацию по модулю.
     *
     * @param string $sModule
     * @param string $sLayer
     */
    public function actionShow($sModule, $sLayer)
    {
        $c = \Yii::$app->register->getModuleConfig($sModule, $sLayer);

        print_r($c->getData());
    }

    /**
     * Отображает все доступные события / заданное.
     *
     * @param string $sEventsName
     */
    public function actionEvents($sEventsName = '')
    {
        $aAllEvents = \Yii::$app->register->getAllEvents();

        if ($sEventsName) {
            if (!isset($aAllEvents[$sEventsName])) {
                $this->stderr("No handlers for event [{$sEventsName}]");
                $aAllEvents = [];
            } else {
                $aAllEvents = [$sEventsName => $aAllEvents[$sEventsName]];
            }
        }

        foreach ($aAllEvents as $sName => $aList) {
            $this->br();
            $this->stdout($sName, Console::BOLD, Console::UNDERLINE);
            $this->br();

            foreach ($aList as $aEvent) {
                $this->stdout(sprintf(
                    '  %s :: %s [set by %s\%s]',
                    $aEvent[config\Vars::EVENT_CLASS],
                    $aEvent[config\Vars::EVENT_METHOD],
                    $aEvent[config\Vars::LAYER_NAME],
                    $aEvent[config\Vars::MODULE_NAME]
                ));

                if (isset($aEvent[config\Vars::EVENT_TO_CLASS])) {
                    $this->stdout(sprintf(
                        ' (listen to class %s)',
                        $aEvent[config\Vars::EVENT_TO_CLASS]
                    ));
                }

                $this->br();
            }
        }

        $this->br();
    }

    /**
     * Перезагружает конфиги всех установленных модулей.
     */
    public function actionUpdate()
    {
        $installer = new installer\Api();

        $installer->updateAllModulesConfig();

        $this->br();
        $this->stdout('All module config updated');
        $this->br(2);
    }
}
