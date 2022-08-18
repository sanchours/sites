<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Messages';
$aConfig['title'] = 'Сообщения';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Система сообщений из SMS для плозадок';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::CONTENT;

return $aConfig;
