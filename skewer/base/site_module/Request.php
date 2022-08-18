<?php

namespace skewer\base\site_module;

use Yii;

/**
 * Менеджер POST(JSON) параметров.
 *
 * @class RequestParams
 * @project Skewer
 * @Author ArmiT, $Author$
 *
 * @version $Revision$
 * @date $Date$
 * @static
 * @singleton
 */
class Request
{
    /**
     * Набор POST параметров.
     *
     * @var array
     */
    private static $aParams = [];

    /**
     * Массив запрашиваемых на выполнение events.
     *
     * @var array
     */
    private static $aEvents = [];

    /**
     * true, если запрос для админского интерфейса.
     *
     * @var bool
     */
    private static $bIsCmsRequest = false;

    /**
     * Идентификатор сессии.
     *
     * @var string
     */
    private static $sSessionId = '';

    /**
     * Экземпляр текущего класса.
     *
     * @var null
     */
    private static $oInstance = null;

    /**
     * Набор JSON заголовков, если пришли.
     *
     * @var array
     */
    private static $aJsonHeaders = [];

    /**
     * Закрываем возможность вызвать clone.
     */
    private function __clone()
    {
    }

    /**
     * Инициализация менеджера входящих параметров.
     *
     * @static
     */
    public static function init()
    {
        if (isset(self::$oInstance) and (self::$oInstance instanceof self)) {
            return self::$oInstance;
        }

        self::$oInstance = new self();

        return self::$oInstance;
    }

    // func

    /**
     * Создает экземпляр менеджера параметров. Обрабатывает POST параметры.
     * В случае обнаружения JSON Package - разбирает его.
     */
    private function __construct()
    {
        //return true;
        /* Обычные post параметры */
        if (count($_POST)) {
            foreach ($_POST as $sKey => $sData) {
                self::$aParams['post'][$sKey] = $sData;
            }
        }

        if (count($_GET)) {
            foreach ($_GET as $sKey => $sData) {
                self::$aParams['get'][$sKey] = $sData;
            }
        }

        $contentType = Yii::$app->getRequest()->getContentType();
        if (($pos = mb_strpos($contentType, ';')) !== false) {
            // e.g. application/json; charset=UTF-8
            $contentType = mb_substr($contentType, 0, $pos);
        }

        // если тип запроса - JSON
        //if($contentType=='application/json') {
        if ($contentType == 'application/json') {
            $aRequest = Yii::$app->getRequest()->getBodyParams();
            self::$bIsCmsRequest = true;

            if (!empty($aRequest['sessionId'])) {
                self::setSessionId($aRequest['sessionId']);
            }

            if (isset($aRequest['data'])) {
                unset($aRequest['data']);
            }

            /*Смотрим есть ли запросы на обработку событий*/
            if (isset($aRequest['events']) && count($aRequest['events'])) {
                foreach ($aRequest['events'] as $sEvent => $aParams) {
                    self::$aEvents[$sEvent] = $aParams;
                }
            }// each param

            self::$aJsonHeaders = $aRequest;
        } else {
            /*Отработка посылки в POST*/

            self::setSessionId(Yii::$app->getRequest()->post('sessionId', false));
            $sPath = Yii::$app->getRequest()->post('path', false);
            if ($sPath) {
                $data = ['data' => [$sPath => Yii::$app->getRequest()->getBodyParams()]];
                Yii::$app->getRequest()->setBodyParams($data);
            }

            // если есть обязательные параметры - взвести флаг
            // если пришел файл, для правильной обработки JSON
            if ($sPath and self::$sSessionId) {
                self::$bIsCmsRequest = true;
            }
        }

        return true;
    }

    // constructor

    /**
     * Возвращает значение строкового параметра по имени и пути меток до него.
     *
     * @param string $sName Имя параметра
     * @param string $sLabelPath Путь меток вызова до него
     * @param string $sDefaultValue Значение, возвращаемое по-умолчанию
     *
     * @return string
     */
    public static function getStr($sName, $sLabelPath = '', $sDefaultValue = null)
    {
        $request = Yii::$app->getRequest();

        $data = $request->getBodyParams();

        if ($sLabelPath && $sLabelPath !== '') {
            if (isset($data['data'][$sLabelPath][$sName])) {
                return $data['data'][$sLabelPath][$sName];
            }
        }

        if ($sName == 'data' && isset($data['data'][$sLabelPath])) {
            return $sDefaultValue;
        }

        if (isset($data[$sName])) {
            return $data[$sName];
        }

        $data = $request->getQueryParams();
        if (isset($data[$sName])) {
            return $data[$sName];
        }

        return $sDefaultValue;
    }

    /**
     * Возвращает true, если запрос в админском интерфейсе.
     *
     * @static
     *
     * @return bool
     */
    public static function isCmsRequest()
    {
        return (bool) self::$bIsCmsRequest;
    }

    /**
     * Отдает набор JSON заголовков.
     *
     * @return array
     */
    public static function getJsonHeaders()
    {
        return self::$aJsonHeaders;
    }

    /**
     * Возвращает идентификатор сессии дерева процессов.
     *
     * @static
     *
     * @return string
     */
    public static function getSessionId()
    {
        return self::$sSessionId;
    }

    /**
     * Задает идентификатор сессии дерева процессов.
     *
     * @static
     * @param string $sessionId
     */
    public static function setSessionId($sessionId)
    {
        self::$sSessionId = $sessionId;
    }

    // func

    /**
     * Возвращает разобранный JSON Package в случае его нахождения либо false.
     *
     * @static
     *
     * @return array|bool
     */
    public static function getJSONPackage()
    {
        return (isset(self::$aParams['json'])) ? self::$aParams['json'] : false;
    }

    // func

    /**
     * Устанавливает новое значение параметра в JSON Package.
     *
     * @static
     *
     * @param string $sLabelPath Путь по меткам вызова
     * @param string $sName Название параметра
     * @param mixed $mValue Значение параметра
     * @param bool $bOverlay Флаг жесткой либо мягкой вставки (перекрывать или нет существующиее значение)
     *
     * @return bool
     */
    public static function set($sLabelPath, $sName, $mValue, $bOverlay = true)
    {
        if ($bOverlay or self::getStr($sName, $sLabelPath) === null) {
            $data = Yii::$app->request->getBodyParams();
            $data['data'][$sLabelPath][$sName] = $mValue;
            Yii::$app->request->setBodyParams($data);
            self::$aParams['json'][$sLabelPath]['params'][$sName] = $mValue;

            return true;
        }

        return false;
    }

    // func
}// class
