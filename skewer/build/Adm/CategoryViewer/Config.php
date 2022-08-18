<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'CategoryViewer';
$aConfig['title'] = 'Список разделов';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Админ-интерфейс управления разводкой';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::ADM;

$aConfig['param_group'] = 'CategoryViewer'; // Группа параметров

return $aConfig;
