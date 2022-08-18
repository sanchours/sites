<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'FAQ';
$aConfig['title'] = 'Модуль вопросов и ответов';
$aConfig['version'] = '2.000a';
$aConfig['description'] = 'Модуль вопросов и ответов';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::PAGE;
$aConfig['languageCategory'] = 'faq';

$aConfig['dependency'] = [
    ['FAQ', Layer::ADM],
];

return $aConfig;
