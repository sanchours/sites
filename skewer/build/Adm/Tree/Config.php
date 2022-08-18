<?php

namespace skewer\build\Adm\Tree;

use skewer\base\section\models\TreeSection;
use skewer\base\site\Layer;
use skewer\build\Tool\SeoGen\exporter\Api as ExporterApi;
use skewer\build\Tool\SeoGen\importer\Api as ImporterApi;
use skewer\components;

/* main */
$aConfig['name'] = 'Tree';
$aConfig['title'] = 'Дерево';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Дерево разделов';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::ADM;

$aConfig['events'][] = [
    'event' => TreeSection::EVENT_BEFORE_DELETE,
    'eventClass' => TreeSection::className(),
    'class' => TreeSection::className(),
    'method' => 'onSectionDelete',
];

$aConfig['events'][] = [
    'event' => components\search\Api::EVENT_GET_ENGINE,
    'class' => TreeSection::className(),
    'method' => 'getSearchEngine',
];

$aConfig['events'][] = [
    'event' => components\search\Api::EVENT_CMS_SEARCH,
    'class' => Api::className(),
    'method' => 'search',
];

$aConfig['events'][] = [
    'event' => ImporterApi::EVENT_GET_LIST_IMPORTERS,
    'class' => TreeSection::className(),
    'method' => 'getImporter',
];

$aConfig['events'][] = [
    'event' => ExporterApi::EVENT_GET_LIST_EXPORTERS,
    'class' => TreeSection::className(),
    'method' => 'getExporter',
];

return $aConfig;
