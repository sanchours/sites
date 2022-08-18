<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

/* main */
$aConfig['name'] = 'GalleryOnPage';
$aConfig['title'] = 'GalleryOnPage';
$aConfig['version'] = '2.000';
$aConfig['description'] = 'Настройка модуля выаода галереи из другого раздела';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::ADMIN;

return $aConfig;
