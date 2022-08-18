<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'AuthSocialNetwork';
$aConfig['title'] = 'Авторизация через социальные сети';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Авторизация через соц. сети';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::ADMIN;
$aConfig['languageCategory'] = 'socialNetwork';

return $aConfig;
