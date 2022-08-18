<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'SeoGen';
$aConfig['version'] = '1.0';
$aConfig['title'] = 'Импорт/экспорт seo данных';
$aConfig['description'] = 'Импорт/экспорт seo данных';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = \skewer\build\Tool\LeftList\Group::SEO;
$aConfig['languageCategory'] = 'SeoGen';

return $aConfig;
