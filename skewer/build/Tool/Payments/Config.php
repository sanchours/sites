<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Payments';
$aConfig['title'] = 'Системы оплат';
$aConfig['version'] = '1.1';
$aConfig['description'] = 'Выбор и настройка систем оплат для сайта';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::ADMIN;

$aConfig['dependency'] = [
    ['Payment', Layer::PAGE],
];

return $aConfig;
