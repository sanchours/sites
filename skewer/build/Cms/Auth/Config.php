<?php

use skewer\base\site\Layer;

$aConfig['name'] = 'Auth';
$aConfig['title'] = 'Авторизация';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Панель авторизации в админке (центральная + панель вверху справа)';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::CMS;
$aConfig['languageCategory'] = 'auth';

return $aConfig;
