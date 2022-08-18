<?php

namespace skewer\build\Adm\Testing;

use skewer\base\site\Layer;

/* main */
$aConfig['name'] = 'Testing';
$aConfig['title'] = 'Тестирование';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Дерево приемочных тестов';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::ADM;
$aConfig['languageCategory'] = 'testing';

return $aConfig;
