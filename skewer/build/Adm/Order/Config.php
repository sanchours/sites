<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Order';
$aConfig['title'] = 'Заказы (админ)';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Админ-интерфейс управления модулем заказов';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::ADM;

$aConfig['events'][] = [
    'event' => skewer\components\modifications\Api::EVENT_GET_MODIFICATION,
    'class' => \skewer\build\Adm\Order\Api::className(),
    'method' => 'getLastMod',
];

return $aConfig;
