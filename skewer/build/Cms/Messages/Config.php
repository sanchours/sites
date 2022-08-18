<?php

use skewer\base\site\Layer;

$aConfig['name'] = 'Messages';
$aConfig['title'] = 'Новые сообщения';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Выводит количество непрочитанных сообщений';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::CMS;

return $aConfig;
