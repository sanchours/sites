<?php

namespace skewer\app;

use yii\helpers\ArrayHelper;

require_once 'CacheDropTrait.php';

/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 05.04.2016
 * Time: 18:14.
 */
class ConsoleApp extends \yii\console\Application
{
    use CacheDropTrait;

    public function init()
    {
        Application::defineWebProtocol();
        parent::init();
    }

    /**
     * Отдает значенеие параметра конфигурации по заданному адресу
     * Метод работает по принципу ArrayHelper::getValue.
     *
     * @param array|string $sPath
     * @param null|mixed $sDefault
     *
     * @return mixed
     */
    public function getParam($sPath, $sDefault = null)
    {
        return ArrayHelper::getValue(\Yii::$app->params, $sPath, $sDefault);
    }
}
