<?php

use skewer\base\site\Layer;

$aConfig['name'] = 'Frame';
$aConfig['title'] = 'Frame';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Общий контейнер для построения админского интерфейса';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::CMS;
$aConfig['languageCategory'] = 'adm';

return $aConfig;
