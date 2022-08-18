<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Title';
$aConfig['title'] = 'Модуль заголовка';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Вывод заголовка страницы';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::PAGE;
$aConfig['languageCategory'] = 'page';

return $aConfig;
