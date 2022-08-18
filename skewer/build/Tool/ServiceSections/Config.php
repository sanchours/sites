<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'ServiceSections';
$aConfig['title'] = 'Системные разделы';
$aConfig['version'] = '1.000';
$aConfig['description'] = 'Набор ID системных разделов для разных языковых веток';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::LANGUAGE;
$aConfig['languageCategory'] = 'languages';

return $aConfig;
