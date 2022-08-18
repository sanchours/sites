<?php

use skewer\base\section\models\TreeSection;
use skewer\base\site\Layer;
use skewer\build\Tool\SeoGen\exporter\Api as ExporterApi;
use skewer\components\gallery\models\Albums;
use skewer\components\search;

/* main */
$aConfig['name'] = 'Gallery';
$aConfig['title'] = 'Галерея (админ)';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Система администрирования фотогаллереи';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::ADM;

$aConfig['events'][] = [
    'event' => TreeSection::EVENT_BEFORE_DELETE,
    'eventClass' => TreeSection::className(),
    'class' => Albums::className(),
    'method' => 'removeSection',
];

$aConfig['events'][] = [
    'event' => search\Api::EVENT_GET_ENGINE,
    'class' => Albums::className(),
    'method' => 'getSearchEngine',
];

$aConfig['events'][] = [
    'event' => \yii\db\ActiveRecord::EVENT_AFTER_UPDATE,
    'eventClass' => \skewer\base\section\models\ParamsAr::className(),
    'class' => 'skewer\components\gallery\Album',
    'method' => 'updateSection',
];

$aConfig['events'][] = [
    'event' => \skewer\build\Tool\SeoGen\importer\Api::EVENT_GET_LIST_IMPORTERS,
    'class' => Albums::className(),
    'method' => 'getImporter',
];

$aConfig['events'][] = [
    'event' => ExporterApi::EVENT_GET_LIST_EXPORTERS,
    'class' => Albums::className(),
    'method' => 'getExporter',
];

$aConfig['cleanup'] = [
    'type' => 'scanDb',
    'cleanupClass' => \skewer\build\Adm\Gallery\Cleanup::className(),
    'specialDirectories' => ['gallery'],
];

return $aConfig;
