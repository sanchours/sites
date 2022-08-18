<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Auth';
$aConfig['title'] = 'Клиенты';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Выводит список зарегистрировавшихся на сайте пользователей';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::ORDER;
$aConfig['languageCategory'] = 'auth';

$aConfig['dependency'] = [
    ['Auth', Layer::PAGE],
    ['Profile', Layer::PAGE],
];

return $aConfig;
