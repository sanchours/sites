<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Schedule';
$aConfig['title'] = 'Планировщик задач';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Интерфейс для управления расписанием задач';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::SYSTEM;

return $aConfig;
