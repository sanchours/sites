<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'SEOTemplates';
$aConfig['title'] = 'SEO шаблоны';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Модуль настройки SEO шаблонов для модулей клиентской части';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::SEO;
$aConfig['languageCategory'] = 'SEO';

return $aConfig;
