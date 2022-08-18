<?php
/**
 * User: kolesnikiv
 * Date: 31.07.13.
 */
use skewer\base\site\Layer;

$aConfig['name'] = 'Dictionary';
$aConfig['title'] = 'Словари';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Управление словарями для каталога';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::CATALOG;
$aConfig['languageCategory'] = 'dict';

return $aConfig;
