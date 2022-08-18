<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'ImportContent';
$aConfig['version'] = '1.0';
$aConfig['title'] = 'Импорт контента ';
$aConfig['description'] = 'Импорт контента из сайтов предыдущих версий';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = \skewer\build\Tool\LeftList\Group::CONTENT;
$aConfig['languageCategory'] = 'ImportContent';

return $aConfig;
