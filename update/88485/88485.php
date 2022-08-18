<?php

use skewer\base\section\Parameters;
use skewer\base\section\params\Type;
use skewer\components\config\PatchPrototype;

class Patch88485 extends PatchPrototype
{
    public $sDescription = 'Создание текстовых полей для предзагрузки ресурсов';

    public $bUpdateCache = false;

    public function execute()
    {
        Parameters::setParams(\Yii::$app->sections->root(), '.', 'preloadResourcesDesktop', '', '', 'Предварительная загрузка файлов для десктопа', Type::paramSystem);
        Parameters::setParams(\Yii::$app->sections->root(), '.', 'preloadResourcesMobile', '', '', 'Предварительная загрузка файлов для мобильных устройств', Type::paramSystem);
    }
}