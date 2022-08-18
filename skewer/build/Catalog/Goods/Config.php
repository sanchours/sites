<?php
/**
 * User: kolesnikov
 * Date: 31.07.13.
 */
use skewer\base\section\models\TreeSection;
use skewer\base\site\Layer;
use skewer\build\Tool\SeoGen\exporter\Api as ExporterApi;
use skewer\build\Tool\SeoGen\importer\Api as ImporterApi;
use skewer\components\catalog\GoodsRow;
use skewer\components\search;

$aConfig['name'] = 'Goods';
$aConfig['title'] = 'Товары';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Управоление всеми товарами сайта';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::CATALOG;
$aConfig['languageCategory'] = 'catalog';

$aConfig['events'][] = [
    'event' => TreeSection::EVENT_BEFORE_DELETE,
    'eventClass' => TreeSection::className(),
    'class' => GoodsRow::className(),
    'method' => 'removeSection',
];

$aConfig['events'][] = [
    'event' => search\Api::EVENT_GET_ENGINE,
    'class' => GoodsRow::className(),
    'method' => 'getSearchEngine',
];

$aConfig['events'][] = [
    'event' => search\Api::EVENT_CMS_SEARCH,
    'class' => \skewer\components\catalog\Api::className(),
    'method' => 'search',
];

$aConfig['events'][] = [
    'event' => ImporterApi::EVENT_GET_LIST_IMPORTERS,
    'class' => GoodsRow::className(),
    'method' => 'getImporter',
];

$aConfig['events'][] = [
    'event' => ExporterApi::EVENT_GET_LIST_EXPORTERS,
    'class' => GoodsRow::className(),
    'method' => 'getExporter',
];

return $aConfig;
