<?php
/**
 * User: kolesnikiv
 * Date: 31.07.13.
 */
use skewer\base\site\Layer;

$aConfig['name'] = 'CardEditor';
$aConfig['title'] = 'Редактор карточек.';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Редактор карточек каталога';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::CATALOG;
$aConfig['languageCategory'] = 'card';

return $aConfig;
