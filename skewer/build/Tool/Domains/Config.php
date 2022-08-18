<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

/*
 * User: kolesnikiv
 * Date: 04.07.12
 * Time: 14:32
 */
$aConfig['name'] = 'Domains';
$aConfig['title'] = 'Домены';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Модуль отображения доменов длля сайта';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::ADMIN;

return $aConfig;
