<?php

use skewer\base\site\Layer;

$aConfig['name'] = 'Forms';
$aConfig['title'] = 'Конструктор форм';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Модуль вывода форм';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::PAGE;

$aConfig['param_settings'] = 'skewer\build\Page\Forms\ParamSettings';

return $aConfig;
