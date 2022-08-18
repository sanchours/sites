<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Subscribe';
$aConfig['title'] = 'Рассылка';
$aConfig['version'] = '2';
$aConfig['description'] = 'Админ-интерфейс управления рассылкой новостей';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::CONTENT;
$aConfig['dependency'] = [
    ['Subscribe', Layer::PAGE],
];

$aConfig['mode'] = [
    'firstState' => 'list', // допустимые состояния: 'user' / 'list'
    'fullButtons' => true,
    'multiTemplates' => true,
    'extFields' => true,
];

return $aConfig;
