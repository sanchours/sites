<?php
/**
 * Константы окружения.
 *
 * @version $Revision: 1878 $
 *
 * @author ArmiT $Author: acat $
 * @project Skewer
 * @date $Date: 2013-04-15 14:47:54 +0400 (Пн, 15 апр 2013) $
 */
/**
 *  О составе путей:.
 *
 *  Площадка в зависимости от назначения может иметь два типа путей до сборки, на которой она работает:
 *  1. Площадка для разработки со своей сборкой (USECLUSTERBUILD = false):
 *
 *  /var/www/<sitename>/skewer/build/
 *                             patches/
 * |______ROOTPATH_____|            - Путь к корневой директории площадки
 * |______RELEASEPATH________|      - Путь к корневой директории релиза(сборки, ядра и патчей)
 * |______BUILDPATH, ______________|- Путь к корневой директориии сборки
 *
 *
 * 2. Площадка в кластере, использующая кластерные сборки (USECLUSTERBUILD = true):
 *
 * /var/skewerCluster/<build Name>/<build Number>/build/
 *                                                core/
 *                                                patches/
 *                   |_BUILDNAME__|                      - Имя сборки
 *                                |_BUILDNUMBER__|       - Номер сборки
 *                   |________BUILDVERSION_______|       - Версия сборки (Имя.номер)
 * |_______________RELEASEPATH___________________|       - Путь к корневой директории релиза в кластере (сборки, ядра и патчей)
 * |_____________BUILDPATH______________________________|- Путь к корневой директории сборки в кластере
 */

/* Группа реагирования на автоматические настройки */

/**
 * @const BUILDNAME string имя сборки
 */
defined('BUILDNAME') or define('BUILDNAME', 'canape');

/*
 * @const BUILDNAME string номер сборки
 */
defined('BUILDNUMBER') or define('BUILDNUMBER', '0000');

/*
 * @const USECLUSTERBUILD bool Указатель на то, что используется сборка, находящаяся в кластере
 */
defined('USECLUSTERBUILD') or define('USECLUSTERBUILD', false);

/*
 * @const CLUSTERGATEWAY string Адрес кластерного шлюза
 */
defined('CLUSTERGATEWAY') or define('CLUSTERGATEWAY', 'http://sms.twinslab.ru/gateway/index.php');

/* Константы путей */

/*
 * @const ROOTPATH string Путь до корневой директории площадки
 */
defined('ROOTPATH') or define('ROOTPATH', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);

/*
 * @const CLUSTERROOTPATH string Путь до корневой директории кластера
 */
defined('RELEASEPATH') or define('RELEASEPATH', ROOTPATH . 'skewer/');

defined('WEBPATH') or define('WEBPATH', ROOTPATH . 'web/');

/*
 * @const BUILDPATH string Путь до корневой директории сборки
 */
defined('BUILDPATH') or define('BUILDPATH', RELEASEPATH . 'build/');

/*
 * @const PATCHPATH string Путь до корневой директории патчей
 */
defined('PATCHPATH') or define('PATCHPATH', ROOTPATH . 'update/');

/* конец группы реагирования на автоматические настройки */

/*
 * @const FILEPATH string Путь до корневой директории загрузки публичных файлов
 */
define('FILEPATH', WEBPATH . 'files/');

/*
 * @const PRIVATE_FILEPATH string Путь до корневой директории загрузки закрытых файлов
 */
define('PRIVATE_FILEPATH', ROOTPATH . 'private_files/');

/*
 * @const IMPORT_FILEPATH string Путь до корневой директории загрузки фотографий для импорта
 */
define('IMPORT_FILEPATH', ROOTPATH . 'import/');

/*
 * @const SITEMAP_FILEPATH string Путь до директории хранения карт сайта
 */

defined('SITEMAP_FILEPATH') or define('SITEMAP_FILEPATH', WEBPATH . 'sitemap_files/');

/*
 * @const WEBROOTPATH string Путь до директории домена (условное определние)
 */
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = '';
}

define('WEBROOTPATH', $_SERVER['HTTP_HOST'] . '/');

/*
 * @const CLUSTERSKEWERPATH string путь до кластера
 */
defined('CLUSTERSKEWERPATH') or define('CLUSTERSKEWERPATH', '/var/skewerCluster/updates/canape/');

/*
 * @const AUTOTEST_BASH_PATH string путь до кластера
 */
defined('AUTOTEST_BASH_PATH') or define('AUTOTEST_BASH_PATH', ROOTPATH . 'tests/autotest/');

/*
 * @const psNew integer Статусы процесса - новый процесс
 */
define('psNew', 0);

/*
 * @const psComplete integer Статусы процесса - процесс отработал в штатном режиме
 */
define('psComplete', 1);

/*
 * @const psWait integer Статусы процесса - процесс ожидает отработки другого процесса
 */
define('psWait', 2);

/*
 * @const psNotFound integer Статусы процесса - процесс на который ссылаются не найден
 */
define('psNotFound', 3);

/*
 * @const psExit integer Статусы процесса - процесс закончил работу с критической ошибкой (404 auth)
 */
define('psExit', 4);

/*
 * @const psRendered integer Статусы процесса - процесс закончил рендеринг данных в шаблон
 */
define('psRendered', 5);

/*
 * @const psRendered integer Статусы процесса - процесс закончил работу с ошибкой
 */
define('psError', 6);

/*
 * @const psAll integer Статусы процесса - любой процесс
 */
define('psAll', 7);

/*
 * @const psReset integer Статусы процесса - сбросить корневой процесс
 */
define('psReset', 8);

/*
 * @const psBreak integer Статусы процесса - остановить выполнение текущего процесса и продолжить выполнять очередь
 */
define('psBreak', 9);

/*Типы шаблонизаторов*/

/*
 * @const parserTwig integer Шаблонизаторы - процесс выбирает шаблонизатор skewer\base\Twig для обработки данных в шаблоне
 */
define('parserTwig', 1);

/*
 * @const parserJSON integer Шаблонизаторы - процесс отправляет данные в менеджер для последующей конвертации в JSON формат
 */
define('parserJSON', 3);

/*
 * @const parserPHP integer Шаблонизаторы - парсер yii
 */
define('parserPHP', 4);

/*
 * @const parserJSONAjax integer Шаблонизаторы - Для парсинга нескольких шаблонов в одном Ajax-запросе
 */
define('parserJSONAjax', 5);

/*Типы вызова*/

/*
 * @const ctModule integer Типы вызова - процесс вызывается как страница
 */
define('ctModule', 12);

/*router predefined pages*/

/*
 * @const page404 integer Предустановленные разделы - раздел 404
 */
define('page404', 21);

/*
 * @const pageAuth integer Предустановленные разделы - раздел авторизации
 */
define('pageAuth', 22);

/*
 * Указывает старше какого срока можно удалять записи лога для пользователей.
 * в днях
 */
define('cleanUserLog', 180);
/*
 * Указывает старше какого срока можно удалять записи лога. Удалит все записи кроме пользователей
 * в днях
 */
define('cleanLog', 30);
/*
 * Максимальное колличество строк лога, которые остаются после очистки
 */
define('logLimit', 1000);
/*
 * Время, после которого очищается лог
 * в месяцах
 */
define('logInputsClear', 1);
/*
 * Количество неверных попыток входа
 */
define('numberInputs', 10);
/*
 * Время бана после определенного количества попыток
 * в минутах
 */
define('timeExcess', 5);

$sRemoteIp = '';
// есть Nginx? Если да
if (isset($_SERVER['HTTP_X_REAL_IP'])) {
    $sRemoteIp = $_SERVER['HTTP_X_REAL_IP'];
} elseif (isset($_SERVER['REMOTE_ADDR'])) {
    // нет Nginx, только Апач
    $sRemoteIp = $_SERVER['REMOTE_ADDR'];
}

/**
 * @const MAX_EXECUTION_TIME максимальное время выполнения скриптов
 */
$maxTime = ini_get('max_execution_time');
if (!$maxTime) {
    $maxTime = 30;
}
defined('MAX_EXECUTION_TIME') or define('MAX_EXECUTION_TIME', $maxTime);
