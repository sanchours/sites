<?php

use skewer\base\section\models\TreeSection;
use skewer\base\site\Layer;
use skewer\build\Adm\FAQ\models;
use skewer\build\Tool\SeoGen\exporter\Api as ExporterApi;
use skewer\build\Tool\SeoGen\importer\Api as ImporterApi;
use skewer\components\search;

/* main */
$aConfig['name'] = 'FAQ';
$aConfig['title'] = 'Модуль вопросов и ответов';
$aConfig['version'] = '2.000a';
$aConfig['description'] = 'Модуль вопросов и ответов. Админка';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::ADM;
$aConfig['languageCategory'] = 'faq';

$aConfig['events'][] = [
    'event' => TreeSection::EVENT_BEFORE_DELETE,
    'eventClass' => TreeSection::className(),
    'class' => models\Faq::className(),
    'method' => 'removeSection',
];
$aConfig['dependency'] = [
    ['FAQ', Layer::PAGE],
];
$aConfig['events'][] = [
    'event' => skewer\components\modifications\Api::EVENT_GET_MODIFICATION,
    'class' => models\Faq::className(),
    'method' => 'getLastMod',
];
$aConfig['events'][] = [
    'event' => search\Api::EVENT_GET_ENGINE,
    'class' => models\Faq::className(),
    'method' => 'getSearchEngine',
];

$aConfig['events'][] = [
    'event' => ImporterApi::EVENT_GET_LIST_IMPORTERS,
    'class' => models\Faq::className(),
    'method' => 'getImporter',
];

$aConfig['events'][] = [
    'event' => ExporterApi::EVENT_GET_LIST_EXPORTERS,
    'class' => models\Faq::className(),
    'method' => 'getExporter',
];

return $aConfig;
