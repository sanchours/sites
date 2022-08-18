<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

/* main */
$aConfig['name'] = 'FormOrders';
$aConfig['title'] = 'Формы: заказы';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Админ-интерфейс управления заказами, поступившими из форм';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::ORDER;
$aConfig['languageCategory'] = 'forms';

return $aConfig;
