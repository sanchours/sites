<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'RecentlyViewed';
$aConfig['version'] = '1.0';
$aConfig['title'] = 'Недавно смотрели';
$aConfig['description'] = 'Модуль для вывода блока недавно просмотренных товаров';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::PAGE;
$aConfig['languageCategory'] = 'RecentlyViewed';

return $aConfig;
