<?php

use skewer\base\site\Layer;

//модуль для редактирования типов доставки и оплаты
//ранее располагался в Adm/Order
$aConfig['name'] = 'DeliveryPayment';
$aConfig['version'] = '1.0';
$aConfig['title'] = 'Оплата и доставка';
$aConfig['description'] = 'Настройка типов оплаты и доставки';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = 'order';

return $aConfig;
