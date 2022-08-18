<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'SiteTester';
$aConfig['title'] = 'Проверка сайта';
$aConfig['version'] = '1.000';
$aConfig['description'] = 'Автоматическое тестирование площадки';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::ADMIN;

return $aConfig;
