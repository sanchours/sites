<?php

use skewer\base\site\Layer;
use skewer\components\auth\Auth;

$aConfig['name'] = 'Cart';
$aConfig['version'] = '1.0';
$aConfig['title'] = 'Корзина';
$aConfig['description'] = 'Вывод корзины';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::PAGE;
$aConfig['languageCategory'] = 'order';

$aConfig['events'][] = [
    'event' => Auth::AFTER_LOGIN,
    'class' => \skewer\components\cart\Api::className(),
    'method' => 'mergeCart',
];

return $aConfig;
