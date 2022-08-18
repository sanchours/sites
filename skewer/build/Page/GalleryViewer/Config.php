<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'GalleryViewer';
$aConfig['title'] = 'Модуль просмотра галерей';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Модуль вывода галерей на страницеи из другого раздела';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::PAGE;
$aConfig['languageCategory'] = 'gallery';

return $aConfig;
