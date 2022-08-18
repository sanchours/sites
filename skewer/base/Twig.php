<?php

namespace skewer\base;

use skewer\base\site_module\Parser;
use skewer\helpers\Html;
use Twig_Autoloader;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

require_once RELEASEPATH . 'libs/Twig/Autoloader.php';

/**
 * Класс-шаблонизатор
 *
 * @source http://twig.kron0s.com/
 *
 * @dependig Twig_Autoloader
 *
 * @notice Обновлено до версии 1.13
 */
class Twig
{
    /**
     * Экземпляр шаблонизатора.
     *
     * @var null|Twig
     */
    private static $instance = null;
    /**
     * Массив путей в директориям шаблонов.
     *
     * @var array
     */
    private static $aTplPath = [];
    /**
     * Путь к директории хранения кеша шаблонизатора.
     *
     * @var string
     */
    private static $sCache = '';
    /**
     * Экземпляр File system class.
     *
     * @var null|\Twig_Loader_Filesystem
     */
    private static $oLoader = null;
    /**
     * Экземпляр Environment class.
     *
     * @var null|\Twig_Environment
     */
    private static $oEnv = null;
    /**
     * Хранилище данных, вставляемых в шаблон.
     *
     * @var array
     */
    private static $aStack = [];
    /**
     * Флаг режима отладки.
     *
     * @var bool
     */
    private static $bDebugMode = false;

    /**
     * Список алиасов зарегистрированных пользовательских функций.
     *
     * @var array
     */
    protected static $userFunctions = [];

    /**
     * Список алиасов зарегистрированных пользовательских фильтров.
     *
     * @var array
     */
    protected static $userFilters = [];

    /**
     * Инициализирует шаблонизатор, устанавливает начальные настройки.
     */
    private function __construct()
    {
        Twig_Autoloader::register();

        //if(!count(self::$aTplPath)) return false;

        $filter = new Twig_SimpleFilter('truncate', static function ($text, $max = 500, $addStr = '...') {
            return self::truncate($text, $max, $addStr, null, true);
        });

        self::$oLoader = new Twig_Loader_Filesystem(self::$aTplPath);
        self::$oEnv = new Twig_Environment(self::$oLoader, [
            'cache' => self::$sCache,
            'debug' => self::$bDebugMode,
            'autoescape' => false,
        ]);

        if (self::$bDebugMode) {
            self::$oEnv->addExtension(new \Twig_Extension_Debug());
        }

        self::$oEnv->addFilter($filter);

        $filter = new Twig_SimpleFilter('price_format', static function ($string) {
            return self::priceFormat($string);
        });

        self::$oEnv->addFilter($filter);

        $filter = new Twig_SimpleFilter('market_format', static function ($string) {
            return self::marketFormat($string);
        });

        self::$oEnv->addFilter($filter);

        $filter = new Twig_SimpleFilter('ampersandReplace', static function ($string) {
            return preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $string);
        });
        self::$oEnv->addFilter($filter);

        $filter = new Twig_SimpleFilter('hasContent', static function ($string) {
            return Html::hasContent($string);
        });

        self::$oEnv->addFilter($filter);

        $filter = new Twig_SimpleFilter('htmlspecialchars', static function ($string) {
            return htmlspecialchars($string);
        });

        self::$oEnv->addFilter($filter);
    }

    // constructor

    /**
     * Запрещаем вызов извне.
     */
    private function __clone()
    {
    }

    // clone

    /**
     * Создает / возвращает экземпляр шаблонизатора.
     *
     * @static
     *
     * @param array $aTplPath Массив путей к директориям шаблонов
     * @param string $sCache Путь к директроии хранения кеша
     * @param bool $bDebugMode Флаг режима отладки
     *
     * @return null|Twig
     */
    public static function Load($aTplPath, $sCache = '', $bDebugMode = false)
    {
        if (isset(self::$instance) and (self::$instance instanceof self)) {
            return self::$instance;
        }
        self::$aTplPath = $aTplPath;
        self::$sCache = (string) $sCache;
        self::$bDebugMode = (bool) $bDebugMode;
        self::$instance = new self();

        return self::$instance;
    }

    // func

    /**
     * Включает режим отладки для шаблонизатора. В случае, если вызов происходит без предварительной инициализации,
     * то метод возвращает false либо true в случае успешной установки режима.
     *
     * @static
     *
     * @return bool
     */
    public static function enableDebug()
    {
        if (!(self::$oEnv instanceof Twig_Environment)) {
            return false;
        }
        self::$bDebugMode = true;
        self::$oEnv->enableDebug();
        self::$oEnv->enableAutoReload();

        return false;
    }

    // func

    /**
     * Выключает режим отладки для шаблонизатора. В случае, если вызов происходит без предварительной инициализации,
     * то метод возвращает false либо true в случае успешной установки режима.
     *
     * @static
     *
     * @return bool
     */
    public static function disableDebug()
    {
        if (!(self::$oEnv instanceof Twig_Environment)) {
            return false;
        }
        self::$bDebugMode = false;
        self::$oEnv->disableDebug();
        self::$oEnv->disableAutoReload();

        return false;
    }

    // func

    /**
     * Возвращает режим работы шаблонизатора. Debug/Production.
     *
     * @static
     *
     * @return bool
     */
    public static function isDebugMode()
    {
        if (!(self::$oEnv instanceof Twig_Environment)) {
            return false;
        }

        return self::$oEnv->isDebug();
    }

    // func

    /**
     * Присваивает имя данным, по которому они будут доступны в шаблоне.
     *
     * @static
     *
     * @param string $sName
     * @param mixed $uValue
     *
     * @return bool
     */
    public static function assign($sName, $uValue)
    {
        if (empty($sName)) {
            return false;
        }
        self::$aStack[$sName] = $uValue;

        return true;
    }

    // func

    /**
     * Устанавливает путь к директории шаблонов.
     *
     * @static
     *
     * @param array $aPaths
     * @param bool $bForceWrite
     *
     * @return bool
     */
    public static function setPath($aPaths = [], /** @noinspection PhpUnusedParameterInspection */
                                   $bForceWrite = true)
    {
        if (!(self::$oLoader instanceof Twig_Loader_Filesystem)) {
            return false;
        }
        self::$oLoader->setPaths($aPaths);

        return true;
    }

    // func

    /**
     * Возвращает код шаблона после обработки шаблонизатором
     *
     * @static
     *
     * @param string $sTplName Ссылка на шаблон в рамках ранее указанной среды
     *
     * @return bool|string
     */
    public static function render($sTplName)
    {
        $oTpl = self::$oEnv->loadTemplate($sTplName);
        $sOut = $oTpl->render(self::$aStack);
        self::$aStack = [];

        return $sOut;
    }

    // func

    /**
     * Возвращает код шаблона после обработки шаблонизатором
     *
     * @param string $sTplSource Код шаблона
     * @param array $aData
     *
     * @return string
     */
    public static function renderSource($sTplSource, $aData = [])
    {
        $twig = new \Twig_Environment(new \Twig_Loader_String(), [
            'cache' => self::$sCache,
            'debug' => self::$bDebugMode,
            'autoescape' => false,
        ]);

        $aParserHelpers = Parser::getParserHelpers();
        if (count($aParserHelpers)) {
            foreach ($aParserHelpers as $sHelperName => $oHelperObject) {
                $aData[$sHelperName] = $oHelperObject;
            }
        }

        $sOut = $twig->render($sTplSource, $aData);

        return $sOut;
    }

    /**
     * Добавляет в пространство имен шаблона функцию с именем $alias и телом $function.
     *
     * @param $alias
     * @param \Closure $function
     *
     * @return bool
     */
    public static function addFunction($alias, \Closure $function)
    {
        if (isset(self::$userFunctions[$alias])) {
            return false;
        }
        self::$userFunctions[$alias] = $alias;

        $func = new Twig_SimpleFunction($alias, $function);
        self::$oEnv->addFunction($func);

        return true;
    }

    /**
     * Добавляет в пространство имен шаблона фильтр с именем $alias и телом $function.
     *
     * @param $alias
     * @param \Closure $function
     *
     * @return bool
     */
    public static function addFilter($alias, \Closure $function)
    {
        if (isset(self::$userFilters[$alias])) {
            return false;
        }
        self::$userFilters[$alias] = $alias;

        $filter = new Twig_SimpleFilter($alias, $function);
        self::$oEnv->addFilter($filter);

        return true;
    }

    /**
     * Форматирование цены.
     *
     * @param $string
     * @param mixed $addSpaces
     *
     * @return int|string
     */
    public static function priceFormat($string, $addSpaces = true)
    {
        if (!is_numeric($string)) {
            return $string;
        }
        $bHidePriceFractional = SysVar::get('catalog.hide_price_fractional');

        if ($addSpaces) {
            return number_format((float) $string, ($bHidePriceFractional) ? 0 : 2, '.', ' ');
        }
        if ($bHidePriceFractional) {
            return (int) number_format((float) $string, 0, '.', '');
        }

        return (float) number_format((float) $string, 2, '.', '');
    }

    /**
     * Форматирование кавычек и прочего для яндекс.маркета.
     *
     * @param $string
     *
     * @return string
     */
    public static function marketFormat($string)
    {
        $string = preg_replace('/&laquo;/', '&lt;&lt;', $string);
        $string = preg_replace('/&raquo;/', '&gt;&gt;', $string);

        return $string;
    }

    /**
     * Обрежет текст до указанного количества символов
     * Алгоритм обрезки:
     * обрезать текст до указан.количества символов
     * Удалить последнее слово( т.к. оно могло быть обрезано )
     * Добавить суффикс
     *
     * @param string $sText
     * @param int $iCount
     * @param string $sSuffix
     * @param null|string $encoding
     * @param bool $asHtml
     *
     * @return string
     */
    public static function truncate($sText, $iCount, $sSuffix = '...', $encoding = null, $asHtml = false)
    {
        return \skewer\helpers\StringHelper::truncate($sText, $iCount, $sSuffix, $encoding, $asHtml);
    }

    /**
     * Очищает файловый кэш.
     */
    public static function clearCache()
    {
        if (self::$oEnv) {
            self::$oEnv->clearCacheFiles();
        }
    }
}// class
