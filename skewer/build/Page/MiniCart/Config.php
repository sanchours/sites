<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'MiniCart';
$aConfig['version'] = '1.0';
$aConfig['title'] = 'Мини-корзина';
$aConfig['description'] = 'Вывод количества товаров';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::PAGE;

$aConfig['dependency'][] = ['Cart', Layer::PAGE];
$aConfig['languageCategory'] = 'order';

return $aConfig;
