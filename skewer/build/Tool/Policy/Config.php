<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Policy';
$aConfig['title'] = 'Политики доступа';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Админ-интерфейс управления политиками доступа';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::ADMIN;
$aConfig['languageCategory'] = 'auth';

$aConfig['policy'] = [
    [
        'name' => 'useControlPanel',
        'default' => 0,
    ],
    [
        'name' => 'useDesignMode',
        'default' => 0,
    ],
    [
        'name' => 'useFormsReachGoals',
        'default' => 0,
    ],
    [
        'name' => 'canSettingButton',
        'default' => 0,
    ],
    [
        'name' => 'canAddSections',
        'default' => 0,
    ],
    [
        'name' => 'canPrivateFiles',
        'default' => 0,
    ],
];

return $aConfig;
