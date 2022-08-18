<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Lang';
$aConfig['title'] = 'Выбор языка';
$aConfig['version'] = '1.000';
$aConfig['description'] = 'Выбор языка в CMS';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::CMS;
$aConfig['languageCategory'] = 'languages';

return $aConfig;
