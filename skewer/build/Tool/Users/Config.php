<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Users';
$aConfig['title'] = 'Пользователи';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Управление пользовательскими аккаунтами';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::ADMIN;
$aConfig['languageCategory'] = 'auth';

return $aConfig;
