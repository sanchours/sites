<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Poll';
$aConfig['title'] = 'Голосование';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Голосование';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::PAGE;

$aConfig['dependency'] = [
    ['Poll', Layer::TOOL],
];

return $aConfig;
