<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Poll';
$aConfig['title'] = 'Голосование';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Админ-интерфейс управления модулем голосования';
$aConfig['revision'] = '0003';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = \skewer\build\Tool\LeftList\Group::CONTENT;

$aConfig['dependency'] = [
    ['Poll', Layer::PAGE],
];

return $aConfig;
