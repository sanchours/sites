<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'RobotsTxt';
$aConfig['version'] = '1.0';
$aConfig['title'] = 'Настройка robots.txt';
$aConfig['description'] = 'Модуль редактирования файла Robots.txt';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = \skewer\build\Tool\LeftList\Group::SEO;
$aConfig['languageCategory'] = 'robotstxt';

return $aConfig;
