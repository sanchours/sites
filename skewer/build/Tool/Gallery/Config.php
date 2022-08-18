<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Gallery';
$aConfig['title'] = 'Галерея. Профили';
$aConfig['version'] = '1.000';
$aConfig['description'] = 'Настройка профилей для галереи';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::ADMIN;

return $aConfig;
