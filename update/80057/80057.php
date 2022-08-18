<?php

use skewer\components\config\PatchPrototype;

class Patch80057 extends PatchPrototype
{
    public $sDescription = 'Установка модулей';

    public $bUpdateCache = false;

    public function execute()
    {
        $this->installModule('SliderBrowser', 'Cms');
        $this->installModule('PolicyBrowser', 'Cms');
    }
}
