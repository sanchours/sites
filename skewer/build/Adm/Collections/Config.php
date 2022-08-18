<?php

use skewer\base\site\Layer;

$aConfig['name'] = 'Collections';
$aConfig['title'] = 'Коллекции (админ)';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Админ-интерфейс управления коллекциями в разделе';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::ADM;

$aConfig['dependency'] = [
    ['Collections', Layer::CATALOG],
    ['ZonesEditor', Layer::ADM],
];

return $aConfig;
