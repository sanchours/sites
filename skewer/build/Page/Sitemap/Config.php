<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Sitemap';
$aConfig['title'] = 'Карта сайта';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Выводит список разделов сайта';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::PAGE;

return $aConfig;
