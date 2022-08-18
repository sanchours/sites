<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

/*
 * User: ArmiT
 */
$aConfig['name'] = 'ModulesManager';
$aConfig['title'] = 'Менеджер модулей';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Менеджер управления модулями';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::ADMIN;

return $aConfig;
