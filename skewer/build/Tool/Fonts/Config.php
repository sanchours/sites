<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Fonts';
$aConfig['title'] = 'Шрифты';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Настройка списка активных шрифтов для сайта';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::CONTENT;
$aConfig['languageCategory'] = 'fonts';

return $aConfig;
