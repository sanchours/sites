<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'YandexExport';
$aConfig['title'] = 'Яндекс.Маркет';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Выгрузка в Яндекс.Маркет';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::CONTENT;

return $aConfig;
