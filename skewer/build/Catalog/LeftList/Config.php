<?php
/**
 * User: kolesnikiv
 * Date: 31.07.13.
 */
use skewer\base\site\Layer;

$aConfig['name'] = 'LeftList';
$aConfig['title'] = 'Список';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Для вывода списка каталожных модулей в панели навигации';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::CATALOG;
$aConfig['languageCategory'] = 'catalog';

$aConfig['policy'] = [[
    'name' => 'useCatalog',
    'default' => 1,
]];

return $aConfig;
