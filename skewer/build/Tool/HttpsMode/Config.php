<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'HttpsMode';
$aConfig['title'] = 'HTTPS соединение';
$aConfig['version'] = '1.000';
$aConfig['description'] = 'Режим работы сайта через протокол https';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::SYSTEM;

return $aConfig;
