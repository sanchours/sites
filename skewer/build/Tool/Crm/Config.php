<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Crm';
$aConfig['title'] = 'Интеграция с CRM';
$aConfig['version'] = '1.000';
$aConfig['description'] = 'Настройки подключения к CRM';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::ORDER;

return $aConfig;
