<?php

namespace skewer\base\router;

use skewer\base\section;
use skewer\base\site\Site;
use skewer\base\SysVar;
use skewer\build\Design\Zones;

/**
 * Класс для управление адресной маршрутизацией.
 *
 * @author ArmiT, $Author$
 *
 * @version $Revision$
 * @date $Date$
 * @project Skewer
 */
class Router
{
    /**
     * Id запрашеваемого раздела.
     *
     * @var int
     */
    public $sectionId = 0;

    /**
     * #42836
     * массив GET-параметров.
     *
     * @var array
     */
    public $aGet = [];

    /**
     * Остаток прешедшего в роутер адреса.
     *
     * @var string
     */
    protected $sCurrentURL = '';

    /**
     * Дата последней модификации страницы.
     *
     * @var string
     */
    protected $iLastModifiedDate = 0;

    /**
     * Создает экземпяр Router.
     *
     * @param $sCurrentURL string
     * @param $aGet array
     *
     * @return Router
     */

    /** @var bool Флаг того, что url был полностью разобран */
    private $bUriParsed = false;

    /**
     * Состояние страницы(детальная и т.п.).
     *
     * @var string
     */
    protected $sStatePage = Zones\Api::DEFAULT_LAYOUT;

    public function __construct($sCurrentURL = null, $aGet = null)
    {
        if ($aGet === null) {
            $this->aGet = $_GET;
        } else {
            $this->aGet = &$aGet;
        }

        if ($sCurrentURL === null) {
            // проверка на то, что запрос был через вэб, а не из консоли
            if (isset($_SERVER['HTTP_X_REWRITE_URL']) or
                isset($_SERVER['ORIG_PATH_INFO']) or
                isset($_SERVER['REQUEST_URI'])
            ) {
                $this->sCurrentURL = \Yii::$app->request->pathInfo;
            } else {
                $this->sCurrentURL = '';
            }
        } else {
            $this->sCurrentURL = &$sCurrentURL;
        }

        return true;
    }

    // constructor

    /**
     * Устанавливает состояние страницы.
     *
     * @param string $sStatePage
     */
    public function setStatePage($sStatePage)
    {
        $this->sStatePage = $sStatePage;
    }

    /**
     * Получает состояние страницы.
     *
     * @return string
     */
    public function getStatePage()
    {
        return $this->sStatePage;
    }

    /**
     * Возвращает id текущего раздела.
     *
     * @param $iDefaultSection integer - Id раздел, загружаемого по-умолчанию
     *
     * @return int
     */
    public function getSection($iDefaultSection)
    {
        $this->sCurrentURL = ltrim(\Yii::$app->request->url, '/');
        $sRequestURI = $this->sCurrentURL;

        /* Убираем все что после ? т.к. оно уже есть в $_GET */
        if (($iPos = mb_strpos($this->sCurrentURL, '?')) !== false) {
            $this->sCurrentURL = mb_substr($this->sCurrentURL, 0, $iPos);
        }

        if (empty($sRequestURI)) {
            $iSectionId = $iDefaultSection;
            $this->sCurrentURL = '';
        } else {
            $iSectionId = section\Tree::getSectionByPath('/' . $sRequestURI, $this->sCurrentURL, \Yii::$app->sections->getDenySections(), false);
        }

        /* Убираем все что после ? т.к. оно уже есть в $_GET */
        if (($iPos = mb_strpos($this->sCurrentURL, '?')) !== false) {
            $this->sCurrentURL = mb_substr($this->sCurrentURL, 0, $iPos);
        }

        return $iSectionId;
    }

    // func

    /**
     * Задает страницу для отображения через константу.
     *
     * @param int $iPage Константа страницы редиректа
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function setPage($iPage = page404)
    {
        $this->setUriParsed();

        switch ($iPage) {
            case page404:
                \Yii::$app->getResponse()->setStatusCode(404);
                $this->sectionId = \Yii::$app->sections->page404();
                break;
            case pageAuth:
                \Yii::$app->getResponse()->setStatusCode(401);
                \Yii::$app->request->setBodyParams(['cmd' => '']);
                $this->sectionId = \Yii::$app->sections->getValue('auth');
                break;
            default:
                throw new \Exception("Unknown page constant [{$iPage}]");
        }// set page

        \Yii::$app->trigger('reload_page_id');

        return true;
    }

    /**
     * Разбирает GET параметры по правилам роутинга.
     *
     * @param $aDecodedRules array Массив разобранных правил роутинга
     *
     * @return bool
     */
    public function getParams($aDecodedRules)
    {
        $aSelectedRule = [];

        if ($aDecodedRules and !empty($this->sCurrentURL)) {
            // выбираем наилучшее правило
            foreach ($aDecodedRules as $sCurrentRule) {
                if (preg_match($sCurrentRule['_reg_exp'], $this->sCurrentURL, $aEntry)) {
                    $i = (isset($aEntry[0])) ? mb_strlen($aEntry[0]) * 1000 : 0;

                    foreach ($sCurrentRule as $key => $val) {
                        if ($key == '_reg_exp') {
                            continue;
                        }

                        switch ($val['type']) {
                            case 'const':
                                $i += 100;
                                break;
                            default:
                                $i += 101;
                                break;
                            case 'not_use':
                                $i += 102;
                                break;
                        }
                    } // foreach

                    $aSelectedRule[$i] = $sCurrentRule;
                } // if preg
            } // foreach

            // если есть подходящие правила - дешифруем параметры
            if (count($aSelectedRule)) {
                krsort($aSelectedRule);
                reset($aSelectedRule);
                $sCurrentRule = current($aSelectedRule);
                $i = 1;

                preg_match($sCurrentRule['_reg_exp'], $this->sCurrentURL, $aEntry);
                $this->sCurrentURL = mb_substr($this->sCurrentURL, mb_strlen($aEntry[0]));

                foreach ($sCurrentRule as $key => $val) {
                    if (is_array($val) && isset($val['type'])) {
                        switch ($val['type']) {
                            case 'set':
                                $this->aGet[$key] = $aEntry[$i];
                                ++$i;
                                break;

                            case 'flag':
                                $this->aGet[mb_substr($key, 1)] = 1;
                                ++$i;
                                break;

                            case 'const':
                                $i++;
                                break;

                            case 'int':
                                $this->aGet[$val['value']] = (int) $aEntry[$i];
                                ++$i;
                                break;

                            case 'str':
                                $this->aGet[$val['value']] = (string) $aEntry[$i];
                                ++$i;
                                break;

                            case 'filtercond':
                                $this->aGet[$val['value']] = (string) $aEntry[$i];
                                ++$i;
                                break;

                            case 'not_use':
                                unset($this->aGet[$key]);
                                ++$i;
                                break;

                            case 'value':
                                $this->aGet[$key] = $val['value'];
                                ++$i;
                                break;
                        }// switch
                    }
                } // foreach
            } // if
        }

        return true;
    }

    // func

    /**
     * Возвращает неразобранный остаток URL.
     *
     * @return string
     */
    public function getURLTail()
    {
        return $this->sCurrentURL;
    }

    // func

    /**
     * Отдает Флаг того, что url был полностью разобран.
     *
     * @return string
     */
    public function getUrlParsed()
    {
        return $this->bUriParsed;
    }

    /**
     * Задает флаг того, что url разобран.
     *
     * @param bool $bParsed
     */
    public function setUriParsed($bParsed = true)
    {
        $this->bUriParsed = $bParsed;
    }

    /**
     * Возвращает разобранные GET параметры.
     *
     * @return array
     */
    public function getURLParams()
    {
        return $this->aGet;
    }

    // func

    /**
     * Разбирает строковое представление правил роутинга.
     *
     * @param $aRules array Массив правил маршрутизации
     * @param string $sDefaultCmd состояние по умолчанию
     *
     * @return array
     */
    public static function decodeRules($aRules, $sDefaultCmd = '')
    {
        if (!count($aRules)) {
            return [];
        }

        $aOut = [];

        foreach ($aRules as $sRule => $sCmd) {
            if (is_int($sRule)) {
                $sRule = $sCmd;
                $sCmd = $sDefaultCmd;
            }

            $aItem = [];
            $sRegexp = '';
            $aRule = explode('/', $sRule);

            foreach ($aRule as $sItem) {
                if (!empty($sItem)) {
                    if (mb_strpos($sItem, '=') > 0) {// параметр - набор
                        $mRuleValue = explode('|', mb_substr($sItem, mb_strpos($sItem, '=') + 1));
                        $sRegexp .= '(' . mb_substr($sItem, mb_strpos($sItem, '=') + 1) . ')\/';
                        $sItem = mb_substr($sItem, 0, mb_strpos($sItem, '='));
                        $sRuleType = 'set';
                    } elseif (mb_strpos($sItem, '#') === 0) { // константа - флаг
                        $sRegexp .= '(' . mb_substr($sItem, 1) . ')\/';
                        $mRuleValue = mb_substr($sItem, 1);
                        $sRuleType = 'flag';
                    } elseif (mb_strpos($sItem, '*') === 0) { // константа - заглушка
                        $sRegexp .= '(' . mb_substr($sItem, 1) . ')\/';
                        $mRuleValue = mb_substr($sItem, 1);
                        $sRuleType = 'const';
                    } elseif (mb_strpos($sItem, '!') === 0) { // Исключение, которое не должно обрабатываться
                        $sRegexp .= '' . mb_substr($sItem, 1) . '\/';
                        $mRuleValue = mb_substr($sItem, 1);
                        $sRuleType = 'not_use';
                    } elseif ($sItem != 0 || $sItem === 0 || mb_strpos($sItem, '(int)') > 0) { // параметр - число
                        $mRuleValue = $sItem;
                        if (mb_strpos($sItem, '(int)') > 0) {
                            $mRuleValue = mb_substr($sItem, 0, mb_strpos($sItem, '(int)'));
                        }

                        $sItem = mb_substr($sItem, 0, mb_strpos($sItem, '(int)'));
                        $sRuleType = 'int';
                        $sRegexp .= '(\d+)\/';
                    } elseif (mb_strpos($sItem, '(filtercond)') === 0) { // Условия фильтра(пример: brand=casio,armani_material=kozha,stal)
                        $mRuleValue = str_replace('(filtercond)', '', $sItem);
                        $sRuleType = 'filtercond';
                        $sRegexp .= '([-%=,;&+.\w]*)\/';
                    } else { // параметр - строка
                        $mRuleValue = str_replace('(str)', '', $sItem);
                        $sRuleType = 'str';
                        $sRegexp .= '([-%+.\w]+)\/';
                    }

                    $aItem[$sItem] = ['type' => $sRuleType, 'value' => $mRuleValue];
                }// rule d`t empty
            }

            // если есть команда и она еще не была задана правилом
            if ($sCmd and !isset($aItem['cmd'])) {
                $aItem['cmd'] = [
                    'type' => 'value',
                    'value' => $sCmd,
                ];
            }

            $aItem['_reg_exp'] = '/^' . $sRegexp . '/u';

            $aOut[] = $aItem;
        }// each patterns

        return $aOut;
    }

    // func

    /*public function URL() {

        if(!func_num_args()) return false;
        $mParams = func_get_args();
        $iSectionId = $mParams[0]; // первый параметр всегда id раздела либо его alias

        unSet($mParams[0]);
        if(count($mParams)) {// остались дополнительные параметры
            foreach($mParams as $sParam) {
                list($sModuleName, $sParams) = explode('?',$sParam);
                mb_parse_str($sParam, $aP);
            }// each
        }// if

        //return $sparams;
    }// func*/

    /**
     * Возвращает базовый URL.
     *
     * @return string
     */
    public function getBaseURL()
    {
        return Site::httpDomain();
    }

    // func

    /**
     * Возвращает правила роутинга для указанного модуля.
     *
     * @param string $sClassName string Название класса модуля
     * @param string $sDefaultCmd состояние по умолчанию
     *
     * @return array|bool Массив правил роутинга
     */
    public static function getRulesByClassName($sClassName, $sDefaultCmd = '')
    {
        if (($oModuleRouting = self::buildRoutingFileName($sClassName)) !== false) {
            /* @var $oModuleRouting RoutingInterface */
            return self::decodeRules($oModuleRouting::getRoutePatterns(), $sDefaultCmd);
        }

        return [];
    }

    // func

    /**
     * Возвращает имя класса с правилами роутинга.
     *
     * @param string $sClassName Название класса модуля
     *
     * @return string
     */
    private static function buildRoutingFileName($sClassName)
    {
        if (empty($sClassName)) {
            return false;
        }

        if (mb_strpos($sClassName, 'Module')) {
            if (!class_exists($sClassName)) {
                return false;
            }

            $oModuleRouting = mb_substr($sClassName, 0, -6) . 'Routing';

            if (!class_exists($oModuleRouting)) {
                return false;
            }
        } else { // если передается только имя модуля
            $sClassName = '\\skewer\\build\\Page\\' . $sClassName . '\\Routing';

            if (!class_exists($sClassName)) {
                return false;
            }

            $oModuleRouting = $sClassName;
        }

        return $oModuleRouting;
    }

    /**
     * Возвращает правила исключений, допустимых остатков урл, на главной странице.
     *
     * @param string $sClassName string Название класса модуля
     * @param string $sDefaultCmd состояние по умолчанию
     *
     * @return array
     */
    public static function getRulesExclusionTails4MainPage($sClassName, $sDefaultCmd = '')
    {
        if (($oModuleRouting = self::buildRoutingFileName($sClassName)) !== false) {
            if (is_subclass_of($oModuleRouting, 'skewer\base\router\ExclusionTailsInterface')) {
                /* @var $oModuleRouting ExclusionTailsInterface */
                return self::decodeRules($oModuleRouting::getRulesExclusionTails4MainPage(), $sDefaultCmd);
            }

            return [];
        }

        return [];
    }

    // func

    /**
     * Фильтр. Применяется к сформированному html. Производит замену адресных конструкций на URL валидные адреса.
     *
     * @param string $sInput Собранная html страница
     *
     * @return string html страница с замененными ссылками
     */
    public static function rewriteURLs($sInput)
    {
        $sp = '/
        (?<linkType>href|rel|action){1}=
        ([\'"]){1}
            (?<link>\[(?:[^"\']+)\])+
        \2/xui';

        $sOut = preg_replace_callback($sp, static function ($aEntry) {
            $sOut = $aEntry['linkType'] . '="' . self::rewriteURL($aEntry['link']) . '"';

            return $sOut;
        }, $sInput);

        return $sOut;
    }

    // func

    /**
     * Собирает ссылку по правилам роутинга согласно адресной конструкции.
     *
     * @static
     *
     * @param $sLink string Адресная конструкция
     *
     * @return string Собранная Ссылка
     */
    public static function rewriteURL($sLink)
    {
        $sLink = mb_substr($sLink, 1, -1);
        $aEntry = explode('][', $sLink);

        $sOut = '';
        $aLostParams = [];

        foreach ($aEntry as $mLink) {
            if (mb_strpos($mLink, '?')) { // is module
                list($sClassName, $sParams) = explode('?', $mLink);
                $aDecodedRules = self::getRulesByClassName($sClassName);
                mb_parse_str($sParams, $aParams);

                // поиск подходящего правила
                $aSelectedRule = [];
                $iSelectedRuleWeight = 0;

                if ($aDecodedRules) {
                    foreach ($aDecodedRules as $aItem) {
                        $iCurrentRuleWeight = 0;

                        foreach ($aItem as $sLexemeName => $aLexemeItem) {
                            if (isset($aLexemeItem['type'])) {
                                switch ($aLexemeItem['type']) {
                                    case 'const':

                                        break;
                                    case 'set':
                                        if (isset($aParams[$sLexemeName])) {
                                            ++$iCurrentRuleWeight;
                                        } else {
                                            $iCurrentRuleWeight = -10000;
                                        }
                                        break;
                                    case 'int':
                                        if (isset($aParams[$sLexemeName])) {
                                            ++$iCurrentRuleWeight;
                                        } else {
                                            $iCurrentRuleWeight = -10000;
                                        }
                                        break;
                                    case 'str':
                                        if (isset($aParams[$sLexemeName])) {
                                            ++$iCurrentRuleWeight;
                                        } else {
                                            $iCurrentRuleWeight = -10000;
                                        }
                                        break;
                                    case 'filtercond':
                                        if (isset($aParams[$sLexemeName])) {
                                            ++$iCurrentRuleWeight;
                                        } else {
                                            $iCurrentRuleWeight = -10000;
                                        }
                                }
                            }
                        }
                        if ($iCurrentRuleWeight > $iSelectedRuleWeight) {
                            $iSelectedRuleWeight = $iCurrentRuleWeight;
                            $aSelectedRule = $aItem;
                        }
                    }// each

                    // создание префикса

                    if ($iSelectedRuleWeight > 0) {
                        foreach ($aSelectedRule as $sLexemeName => $aLexemeItem) {
                            if (isset($aLexemeItem['type'])) {
                                switch ($aLexemeItem['type']) {
                                    case 'const':
                                        $sOut .= $aLexemeItem['value'] . '/';
                                        break;
                                    case 'set':
                                        $sOut .= $aParams[$sLexemeName] . '/';
                                        unset($aParams[$sLexemeName]);
                                        break;
                                    case 'int':
                                        $sOut .= $aParams[$sLexemeName] . '/';
                                        unset($aParams[$sLexemeName]);
                                        break;
                                    case 'str':
                                        $sOut .= $aParams[$sLexemeName] . '/';
                                        unset($aParams[$sLexemeName]);
                                        break;
                                    case 'filtercond':
                                        $sOut .= $aParams[$sLexemeName] . '/';
                                        unset($aParams[$sLexemeName]);
                                        break;
                                }
                            }
                        }
                    }// each
                }// if decode rules

                /*Аккумулируем необработанные параметры */
                $aLostParams = $aLostParams + $aParams;
            } elseif (mb_strpos($mLink, '#')) { // is data uid
                //list($sModule, $iId) = explode('#', $mLink);
            } elseif (($mLink)) { // is section
                $sSectionPath = section\Tree::getSectionAliasPath((int) $mLink, false, true);
                $sOut .= ($sSectionPath) ? $sSectionPath : '/';
            }
        }// each

        /* После преобразования остались незадействованные параметры - дописываем в конец */
        if (count($aLostParams)) {
            $aGet = [];
            foreach ($aLostParams as $sKey => $sValue) {
                if (is_array($sValue)) {
                    foreach ($sValue as $sWKey => $sWVal) {
                        $aGet[] = sprintf('%s[%s]=%s', $sKey, $sWKey, $sWVal);
                    }
                } else {
                    $aGet[] = $sKey . '=' . urlencode($sValue);
                }
            }

            $sOut .= (count($aGet)) ? '?' . implode('&', $aGet) : '';
        }// if count params

        return $sOut;
    }

    // func

    /**
     *  Ищет разобранный GET целочисленный параметр и возвращает его по ссылке и true результатом выполнения функции и false в противном случае.
     *
     * @param string $sName Имя запрашиваемого параметра
     * @param int $iValue Значение, возвращаемое в случае отсутствия параметра
     *
     * @return bool
     */
    public function getInt($sName, &$iValue)
    {
        if (isset($this->aGet[$sName])) {
            $iValue = (int) $this->aGet[$sName];

            return true;
        }

        return false;
    }

    // func

    /**
     * Ищет разобранный GET строковый параметр и возвращает его по ссылке и true результатом выполнения функции и false в противном случае.
     *
     * @param string $sName Имя запрашиваемого параметра
     * @param string $sValue Значение, возвращаемое в случае отсутствия параметра
     *
     * @return bool
     */
    public function getStr($sName, &$sValue)
    {
        if (isset($this->aGet[$sName])) {
            $sValue = $this->aGet[$sName];
            /* @deprecated get_magic_quotes_gpc нельзя использовать */
            //if (get_magic_quotes_gpc()) $sValue = stripslashes($sValue);
            return true;
        }

        return false;
    }

    // func

    public function set($sName, $mValue, $bOverlay = true)
    {
        if ($bOverlay or !isset($this->aGet[$sName])) {
            $this->aGet[$sName] = $mValue;

            return true;
        }

        return false;
    }

    // func

    /**
     * Преобразует выходной код для страницы
     * Заменяет адресные конструкции на корректные адреса
     * Протзмодит дополнительные модификации текста.
     *
     * @param string $sIn
     *
     * @return string
     */
    public function modifyOut($sIn)
    {
        $sOut = $sIn;

        // замена ссылок
        $sOut = $this->rewriteURLs($sOut);

        // замена пути к CKEditor
        if (mb_strpos($sOut, '%_CKEDITOR_WEB_DIR_%')) {
            $CkePath = \skewer\libs\CKEditor\AssetForReactAdmin::register(\Yii::$app->getView())->baseUrl;
            $sOut = str_replace('%_CKEDITOR_WEB_DIR_%', $CkePath, $sOut);
        }

        return $sOut;
    }

    /**
     * Отправляет заголовок "Last-Modified"
     * Также будет отправлен заголовок "304 Not Modified",  в случае если
     * клиент запросил страницу с датой изменения больше либо равной iLastModified.
     */
    public function sendHeaderLastModified()
    {
        $iIfModifiedSince = false;

        if (!empty($_ENV['HTTP_IF_MODIFIED_SINCE'])) {
            $iIfModifiedSince = strtotime($_ENV['HTTP_IF_MODIFIED_SINCE']);
        }

        if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $iIfModifiedSince = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
        }

        if ($iIfModifiedSince && ($iIfModifiedSince >= $this->iLastModifiedDate)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
            exit;
        }
        // Формат даты RFC1123
        header('Last-Modified: ' . date('D, d M Y H:i:s O', $this->iLastModifiedDate));
    }

    /**
     * Установить дату последней модификации страницы.
     *
     * @param array | object | string | integer | bool $mLastModified
     *
     * @return bool
     */
    public function setLastModifiedDate($mLastModified)
    {
        if (!$mLastModified) {
            return false;
        }

        if (is_array($mLastModified)) {
            foreach ($mLastModified as $item) {
                if (is_object($item)) {
                    $sLastModified = $item->last_modified_date;
                } elseif (is_array($item)) {
                    $sLastModified = $item['last_modified_date'];
                } else {
                    $sLastModified = $item;
                }

                $iLastModified = is_numeric($sLastModified) ? (int) $sLastModified : strtotime($sLastModified);

                if (!$iLastModified) {
                    continue;
                }

                if ($iLastModified > $this->iLastModifiedDate) {
                    $this->iLastModifiedDate = $iLastModified;
                }
            }

            return true;
        }

        if (is_object($mLastModified)) {
            $sLastModified = $mLastModified->last_modified_date;
        } else {
            $sLastModified = $mLastModified;
        }

        $iLastModified = is_numeric($sLastModified) ? (int) $sLastModified : strtotime($sLastModified);

        if (!$iLastModified) {
            return false;
        }

        if ($iLastModified > $this->iLastModifiedDate) {
            $this->iLastModifiedDate = $iLastModified;
        }

        return true;
    }

    /**
     * Обновить дату модификации сайта.
     *
     * @param int $timestamp - дата. Если параметр не указан, то устанавливается текущая дата
     */
    public function updateModificationDateSite($timestamp = null)
    {
        if (!$timestamp) {
            $timestamp = time();
        }

        SysVar::set('site.last_modified_date', date('Y-m-d H:i:s', $timestamp));
    }
}
