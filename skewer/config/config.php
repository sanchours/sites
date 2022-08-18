<?php

/*
 * Типовая конфигурация приложения
 */

$aConfig = [];

/*main*/

$aConfig['path']['root'] = ROOTPATH;
$aConfig['url']['root'] = WEBROOTPATH;

/* Пользователи */
$aConfig['users'][0] = [
    'login' => '',
    'pass' => '',
];

/*cache*/

/* @config string cache.rootPath Путь к корневой директории хранения файлового кеша  */
$aConfig['cache']['rootPath'] = ROOTPATH . 'cache/';

/*Security*/

/* @config string security.vector Вектор шифрования для Blowfish. Используется в генерации и проверке подписей
 *  Gateway */
$aConfig['security']['vector'] = 'x03nMwK34x&ciSUH0I1got';

/*debug mode*/

/* @config boolean debug.parser Флаг отладки для парсера
 * (Шаблоны компилируются постоянно)
 */
$aConfig['debug']['parser'] = false;

/* Настройки логирования */

/* @config boolean log.enable Флаг включения логирования
 */
$aConfig['log']['enable'] = true;

/* @config boolean log.users Флаг включения логирования действий пользователей
 */
$aConfig['log']['users'] = true;

/* @config boolean log.cron Флаг включения логирования планировщика заданий
 */
$aConfig['log']['cron'] = true;

/* @config boolean log.system Флаг включения логирования системного журнала
 */
$aConfig['log']['system'] = true;

/* @config boolean log.debug Флаг включения логирования журнала отладки
 */
$aConfig['log']['debug'] = true;

/* allow browsers */
/* Наименьшая допустимая версия браузера, поддерживаемая системой */
$aConfig['browser']['Opera'] = 32.0;

$aConfig['browser']['Firefox'] = 24.0;

$aConfig['browser']['Mozilla'] = 5.0;

$aConfig['browser']['Chrome'] = 28;

$aConfig['browser']['Safari'] = 5;

$aConfig['browser']['Edge'] = 79;

$aConfig['browser']['Yandex'] = 20;

/*auth*/
/* @config integer id группы публичного пользователя */
$aConfig['auth']['public_default_id'] = 2;

/* @config integer id группы авторизованного пользователя */
$aConfig['auth']['group_user_id'] = 3;

/*session*/

/* @config string Session.tickets.key Ключ сессии для хранения тикетов класса SessionTicket */
$aConfig['session']['tickets']['key'] = '_tickets';

/* @config string Session.process.key Ключ сессии для хранения дерева процессов класса skewer\base\site_module\ProcessSession */
$aConfig['session']['process']['key'] = '_processSession';
$aConfig['session']['process']['designKey'] = '_designProcessSession';

$aConfig['notifications']['noreplay_email'] = 'no-reply@' . str_replace('/', '', WEBROOTPATH);

/* Parser */

$aConfig['parser']['default']['paths'] = [BUILDPATH . 'common/templates/'];

/* Upload options */

/* @config array upload.form.maxsize Максимально допустимый размер для загрузки файлов в формы в байтах */
$aConfig['upload']['form']['maxsize'] = 64 * 1024 * 1024;

/* @config array upload.maxsize Максимально допустимый размер для загрузки изображений в байтах */
$aConfig['upload']['maxsize'] = 100 * 1024 * 1024;

/* @config array upload.images.maxWidth Максимально допустимый размер для загрузки изображений px по ширине */
$aConfig['upload']['images']['maxWidth'] = 6000;

/* @config array upload.images.maxHeight Максимально допустимый размер для загрузки изображений px по высоте */
$aConfig['upload']['images']['maxHeight'] = 6000;

/* @config array upload.allow.images Список разрешенных для загрузки расширений файлов изображений */
$aConfig['upload']['allow']['images'] = ['jpg', 'jpeg', 'gif', 'png', 'ico', 'webp'];

/* @config array upload.allow.images Список разрешенных для загрузки расширений flash-файлов */
$aConfig['upload']['allow']['flash'] = ['swf', 'flv'];

/* @config array upload.allow.images Список разрешенных для загрузки расширений media-файлов */
$aConfig['upload']['allow']['media'] = [
    'aiff', 'asf', 'avi', 'bmp', 'fla', 'flv', 'gif', 'jpeg', 'jpg', 'mid',
    'mov', 'mp3', 'mp4', 'mpc', 'mpeg', 'mpg', 'png', 'qt', 'ram', 'rm',
    'rmi', 'rmvb', 'swf', 'tif', 'tiff', 'wav', 'wma', 'wmv',
];

/* @config array upload.allow.images Список разрешенных для загрузки расширений файлов */
$aConfig['upload']['allow']['files'] = [
    '7z', 'aiff', 'asf', 'avi', 'bmp', 'csv', 'doc', 'docx', 'fla', 'flv', 'gif',
    'gz', 'gzip', 'ico', 'jpeg', 'jpg', 'mid', 'mov', 'mp3', 'mp4', 'mpc', 'mpeg',
    'mpg', 'ods', 'odt', 'pdf', 'png', 'ppt', 'pptx', 'pxd', 'qt', 'ram', 'rar',
    'rm', 'rmi', 'rmvb', 'rtf', 'sdc', 'sitd', 'swf', 'sxc', 'sxw', 'tar', 'tgz',
    'tif', 'tiff', 'txt', 'vsd', 'wav', 'wma', 'wmv', 'xls', 'xlsx', 'xml', 'zip',
    // для верстальщиков
    'js', 'css', 'ttf', 'svg', 'eot', 'woff', 'webp'
];

/* @config array upload.allow.files_form Список разрешенных для загрузки расширений файлов c формы */
$aConfig['upload']['allow']['files_form'] = [
    'jpg', 'jpeg', 'gif', 'png', 'ico', 'bmp',
    'swf', 'flv', 'avi', 'fla', 'mid', 'mov', 'mp3',
    'mp4', 'mpc', 'mpeg', 'mpg', 'rm', 'tif', 'tiff', 'wav', 'wma', 'wmv',
    'csv', 'doc', 'docx', 'pdf', 'ppt', 'pptx', 'rtf', 'txt', 'xls', 'xlsx', 'xml',
    'asf', 'ods', 'odt', 'pxd', 'sdc', 'sitd', 'sxc', 'sxw', 'vsd',
];

/* @config array upload.allow.images Список разрешенных для загрузки mime-tipe файлов */
$aConfig['upload']['allow']['mimetipefiles'] = [
    'application/json', 'application/javascript', 'application/ogg',
    'application/pdf', 'application/font-woff', 'application/xhtml+xml',
    'application/xml-dtd', 'application/xop+xml',
    'application/xml', 'image/gif', 'image/jpeg',
    'image/pjpeg', 'image/png', 'image/svg+xml', 'image/tiff', 'image/vnd.microsoft.icon',
    'image/vnd.wap.wbmp', 'image/webp', 'text/css', 'text/csv', 'text/html',
    'text/javascript', 'text/plain', 'text/xml', 'audio/mp4', 'video/mp4', 'audio/mpeg',
    'video/mpeg', 'application/excel', 'application/vnd.ms-excel',
    'application/x-excel', 'application/x-msexcel',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/x-shockwave-flash', 'video/x-flv',
    //bmp
    'image/bmp', 'image/x-bmp', 'image/x-bitmap', 'image/x-xbitmap',
    'image/x-win-bitmap', 'image/x-windows-bmp', 'image/ms-bmp', 'image/x-ms-bmp',
    'application/bmp', 'application/x-bmp', 'application/x-win-bitmap',
    //MID
    'audio/mid', 'audio/m', 'audio/midi', 'audio/x-midi', 'application/x-midi', 'audio/soundtrack',
    //MOV
    'video/quicktime', 'video/x-quicktime', 'image/mov', 'audio/aiff', 'audio/x-wav',

    'image/tiff', 'image/x-tiff', 'image/tiff', 'image/x-tiff',
    'video/avi', 'application/x-troff-msvideo', 'application/x-troff-msvideo',
    'video/x-msvideo', 'audio/ogg', 'audio/mpeg3', 'audio/x-mpeg-3', 'video/mpeg', 'video/x-mpeg',
    'application/x-project', 'application/vnd.rn-realmedia', 'audio/x-pn-realaudio',
    'audio/wav', 'audio/x-wav', 'audio/vnd.wave',
    //WMA
    'audio/x-ms-wma', 'video/x-ms-asf',
    //WMV
    'video/x-ms-wmv',

    'audio/x-mid', 'x-music/x-midi',
    'application/vnd.ms-powerpoint',
    'application/x-mspowerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    //RTF
    'application/rtf', 'application/x-rtf', 'text/rtf', 'text/richtext', 'application/msword', 'application/doc', 'application/x-soffice',

    'video/x-ms-asf',
    //ODS
    'application/vnd.oasis.opendocument.spreadsheet', 'application/x-vnd.oasis.opendocument.spreadsheet',
    //PPT
    'application/vnd.ms-powerpoint [official]', 'application/mspowerpoint', 'application/ms-powerpoint', 'application/mspowerpnt', 'application/vnd-mspowerpoint', 'application/powerpoint', 'application/x-powerpoint', 'application/x-m',
    //PPTX
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    //ODT
    'application/vnd.oasis.opendocument.text', 'application/x-vnd.oasis.opendocument.text',

    'application/vnd.stardivision.calc', 'application/x-starcalc',
    //SXC
    'application/vnd.sun.xml.calc',
    //SXW
    'application/vnd.sun.xml.writer',
    //ICO
    'image/ico', 'image/x-icon', 'application/ico', 'application/x-ico', 'application/x-win-bitmap', 'image/x-win-bitmap', 'application/octet-stream',
    //VSD
    'application/vnd.visio',
];

/* @config array page.503 Страницы - заглушки */
$aConfig['page']['503'] = '503.twig';

/* @config array language настрйка языка */
$aConfig['language']['path'] = ROOTPATH . 'cache/language/';

/*
 * URL для глобальной авторизации
 */
$aConfig['token_url'] = 'https://tokens.canape-id.com/api/';

$aConfig['dict_on_page'] = 20;

return $aConfig;
