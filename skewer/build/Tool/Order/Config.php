<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Order';
$aConfig['title'] = 'Заказы';
$aConfig['version'] = '1.1';
$aConfig['description'] = 'Админ-интерфейс управления модулем заказов';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::ORDER;

$aConfig['dependency'] = [
    ['Cart', Layer::PAGE],
    ['MiniCart', Layer::PAGE],
    ['Auth', Layer::PAGE],
    ['Auth', Layer::TOOL],
    ['Profile', Layer::PAGE],
    ['Payments', Layer::TOOL],
    ['DeliveryPayment', Layer::TOOL],
];

return $aConfig;
