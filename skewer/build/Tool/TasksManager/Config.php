<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'TasksManager';
$aConfig['title'] = 'Процессы';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Админ-интерфейс управления процессами';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::SYSTEM;

return $aConfig;
