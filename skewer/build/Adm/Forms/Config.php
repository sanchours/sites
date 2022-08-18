<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Forms';
$aConfig['title'] = 'Конструктор форм (админ)';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Админ-интерфейс управления конструктором форм';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::ADM;

$aConfig['events'][] = [
    'event' => 'target_delete',
    'class' => skewer\build\Adm\Forms\Api::className(),
    'method' => 'checkTarget',
];

return $aConfig;
