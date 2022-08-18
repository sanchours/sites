<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

/* main */
$aConfig['name'] = 'Import';
$aConfig['title'] = 'Импорт и обновление товаров';
$aConfig['version'] = '2.0';
$aConfig['description'] = 'Админ-интерфейс управления импортом и обновлением товаров';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::CONTENT;

$aConfig['cleanup'] = [
    'type' => 'scanDb',
    'cleanupClass' => \skewer\build\Tool\Import\Cleanup::className(),
    'specialDirectories' => [\skewer\components\import\field\File::FolderImportFiles,
                             \skewer\components\import\field\Gallery::ImageDir, ],
];

return $aConfig;
