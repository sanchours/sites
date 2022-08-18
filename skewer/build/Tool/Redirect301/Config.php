<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

/* main */
$aConfig['name'] = 'Redirect301';
$aConfig['title'] = 'Управление редиректами 301';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Управление редиректами 301';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::SEO;

return $aConfig;
