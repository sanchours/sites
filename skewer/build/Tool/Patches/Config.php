<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Patches';
$aConfig['title'] = 'Обновления';
$aConfig['version'] = '1.1';
$aConfig['description'] = 'Модуль для установки патчей БД для площадок';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::SYSTEM;

return $aConfig;
