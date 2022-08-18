<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Payment';
$aConfig['version'] = '1.0';
$aConfig['title'] = 'Системы оплат';
$aConfig['description'] = 'Уведомление о статусе оплаты';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::PAGE;
$aConfig['languageCategory'] = 'payments';

$aConfig['dependency'] = [
    ['Payments', Layer::TOOL],
];

return $aConfig;
