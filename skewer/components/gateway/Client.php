<?php

namespace skewer\components\gateway;

use ReflectionMethod;
use skewer\base\log\Logger;
use skewer\helpers\Validator;

/**
 * Класс, обеспечивающий общение с удаленным сервером
 */
class Client extends Prototype
{
    /**
     * URL для запроса к серверу.
     *
     * @var string
     */
    private $sServerHost = '';

    /**
     * URL клиента.
     *
     * @var string
     */
    private $sClientHost = '';

    /**
     * Версия протокола.
     *
     * @var float
     */
    private $fVersion = 1.0;

    /**
     * Список путей к файлам для отправки.
     *
     * @var array
     */
    protected $aFilesPaths = [];

    /**
     * Массив функций обратного вызова по приходу ответа от сервера.
     *
     * @var array of callable|callback
     */
    protected $aCallbacks = [];

    /**
     * Содержит текст ошибки либо null в случае ее отсутствия.
     *
     * @var null|string
     */
    protected $mError;

    /** @var int идентификатор площадки */
    protected $iClientId = 0;

    /**
     * Конструктор клиента.
     *
     * @param string $sServerHost URL адрес шлюза сервера
     * @param string $iClientId id площадки
     * @param int $iStreamMode режим шифрования
     * @param float $fVersion Версия транспорта
     *
     * @throws Exception
     */
    public function __construct($sServerHost, $iClientId, $iStreamMode = self::StreamTypeEncrypt, $fVersion = 1.0)
    {
        if (!Validator::isUrl($sServerHost)) {
            throw new Exception("Not valid server host [{$sServerHost}]");
        }
//        if(!$iClientId)
//            throw new skewer\components\gateway\Exception("Site Id is empty");

        $this->iClientId = $iClientId;
        $this->sServerHost = $sServerHost;
        $this->iStreamType = (int) $iStreamMode;

        $this->fVersion = (float) $fVersion;

        return true;
    }

    // construct

    /**
     * Добавляет к запросу дополнительные заголовки.
     *
     * @param string $sName Имя параметра заголовка
     * @param mixed $mValue Значение параметра заголовка
     *
     * @return array
     */
    public function addHeader($sName, $mValue = null)
    {
        return $this->aHeader[$sName] = $mValue;
    }

    // func

    /**
     *  Отправляет запрос на выполнение метода $sMethodName класса $sClassName с параметрами $aParameters.
     *
     * @param string $sClassName Имя класса, содержащего вызываемый метод
     * @param string $sMethodName Имя вызываемого метода
     * @param array $aParameters Параметры вызова метода
     * @param null|callable|callback $mCallback
     *
     * @return bool
     */
    public function addMethod($sClassName, $sMethodName, $aParameters = null, $mCallback = null)
    {
        $aAction = [
            'Class' => $sClassName,
            'Method' => $sMethodName,
            'Parameters' => ($aParameters !== null && is_array($aParameters)) ? $aParameters : null,
        ];

        $this->aActions[] = $aAction;
        $this->aCallbacks[] = (is_callable($mCallback)) ? $mCallback : null;

        return true;
    }

    // func

    /**
     * Отправляет файл $sFilePath на сервер. Если указаны $sClassName и $sMethodName, то они будут вызванны после загрузки
     * файла на сервер.
     *
     * @param $sFileName
     * @param string $sFilePath Абсолютный путь к отправляемому на сервер файлу
     * @param string $sClassName Имя класса, содержащего вызываемый метод
     * @param string $sMethodName Имя вызываемого метода
     * @param array $aParameters Параметры, передаваемые методу
     *
     * @throws Exception
     *
     * @return bool
     */
    public function addFile($sFileName, $sFilePath, $sClassName = null, $sMethodName = null, $aParameters = [])
    {
        try {
            if (!file_exists($sFilePath)) {
                throw new Exception('Error sending file: File [' . $sFilePath . '] not found!');
            }
            $this->aFilesPaths[$sFileName] = $sFilePath;

            $aFile = [
                'File' => basename($sFilePath),
                'FileName' => $sFileName,
                'Class' => $sClassName,
                'Method' => $sMethodName,
                'Parameters' => ($aParameters !== null && is_array($aParameters)) ? $aParameters : null,
            ];

            $this->aFiles[] = $aFile;
        } catch (Exception $e) {
            Logger::dumpException($e);

            return false;
        }

        return true;
    }

    // func

    /**
     * Собираем пакет запроса.
     *
     * @throws Exception
     *
     * @return bool|string
     */
    protected function makePackage()
    {
        if (!$this->aActions) {
            return false;
        }

        /* Собираем тело запроса */
        $aData['Actions'] = $this->aActions;
        $aData['Files'] = $this->aFiles;

        if (!function_exists('json_encode')) {
            throw new Exception('Make package error: JSON library is not install!');
        }
        $sData = json_encode($aData);

        /* Собираем заголовки */
        $aPackage = [];
        $aPackage['Header']['Version'] = $this->fVersion;
        $aPackage['Header']['Client'] = $this->sClientHost;
        $aPackage['Header']['ClientId'] = $this->iClientId;
        $aPackage['Header']['Certificate'] = $this->makeCertificate($this->sClientHost, $sData);

        $sData = $this->encryptData($sData, $aPackage['Header']['crypted']);

        $aPackage['Data'] = $sData;

        $sPackage = json_encode($aPackage);

        return $sPackage;
    }

    // func

    /**
     * Отправляет запрос к серверу и возвращает текст ответа либо генерирует исключение
     * типа skewer\components\gateway\Exception в случае
     * возникновения ошибки.
     *
     * @throws Exception
     *
     * @return mixed
     */
    protected function sendRequest()
    {
        if (!in_array('curl', get_loaded_extensions())) {
            throw new Exception('Request error: CURL library is not installed!');
        }
        $aPackage['_gateway_request'] = $this->makePackage();
        if (count($this->aFilesPaths)) {
            foreach ($this->aFilesPaths as $sFileName => $sFilePath) {
                $aPackage[$sFileName] = '@' . $sFilePath;
            }
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->sServerHost);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $aPackage);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60 * 5);

        /* Вызываем метод стандартного обработчика */
        $sResponse = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Connection error: ' . curl_error($ch));
        }
        curl_close($ch);

        return $sResponse;
    }

    // func

    /**
     * Выполняет callback функции для по ответам
     *
     * @param array $aResponse Массив ответов
     *
     * @throws ExecuteException
     * @throws Exception
     */
    protected function executeResponse($aResponse)
    {
        if (!count($aResponse)) {
            throw new Exception('Execute response error: Response is empty!');
        }
        foreach ($aResponse as $iKey => $aAnswer) {
            /* Обработчика нет - пропускаем */
            if (!isset($this->aCallbacks[$iKey])) {
                continue;
            }

            /* если передана строка или функция - вызываем */
            if (!is_array($this->aCallbacks[$iKey]) and is_callable($this->aCallbacks[$iKey])) {
                /** @var callable|callback $cCallback */
                $cCallback = $this->aCallbacks[$iKey];
                $cCallback($aAnswer['response'], $aAnswer['error']);
                continue;
            }

            /* Т.к. callable то может быть объектом либо именем */
            $mClass = $this->aCallbacks[$iKey][0];
            $sMethod = $this->aCallbacks[$iKey][1];

            $oCalledMethod = new ReflectionMethod($mClass, $sMethod);

            /* Метод найден */
            if (!($oCalledMethod instanceof ReflectionMethod)) {
                throw new Exception('Error executing response: Method [' . $sMethod . '] in class [' . $mClass . '] not found!');
            }
            /* И он публичный */
            if (!$oCalledMethod->isPublic()) {
                throw new ExecuteException('Error executing response: Method [' . $sMethod . '] in class [' . $mClass . '] not accessible!');
            }
            /* Учимся принимать ответы - разбирать, запускать callback на каждый из них.
               Каждая callback функция должна цметь принимать два параметра
               1. ответ от обработчика
               2. Экземпляр ошибки, если таковая произошла

           В случае, если обработчик отработал нормально, вернется ответ и null вместо исключения
           В противном случае в ответе будет null а в Исключении тот экземпляр, который его выкинул
            */

            /* Пытаемся выполнить - первым параметром ответ от сервера, вторым исключение если оно было */

            if (!is_object($mClass)) {
                $mClass = new $mClass();
            }
            $oCalledMethod->invokeArgs($mClass, [$aAnswer['response'], $aAnswer['error']]);
        }
    }

    // func

    /**
     * Разбирает ответ от сервера.
     *
     * @param string $sResponse
     *
     * @throws Exception
     *
     * @return null|array
     */
    protected function parseResponse($sResponse)
    {
        $aResponse = json_decode($sResponse, true);

        if (!$this->checkFormatResponse($aResponse)) {
            throw new Exception('Response error: Response has wrong format!');
        }
        $this->aHeader = $aResponse['Header'];

        $aData = $this->decryptData($aResponse['Data'], $aResponse['Header']['crypted']);

        if (!$aData) {
            throw new Exception('Response error: Response has not results or keys not equal!');
        }
        /* Статус ответа больше 200 */
        if ($this->aHeader['Status'] > 200) {
            throw new Exception('Response error: ' . $aData['error']);
        }

        return $aData;
    }

    // func

    /**
     * Выполняет запрос к серверу.
     *
     * @throws Exception
     *
     * @return bool
     */
    public function doRequest()
    {
        try {
            $sResponse = $this->sendRequest();

            if (empty($sResponse)) {
                throw new Exception('Response error: Empty response!');
            }
            $aResponse = $this->parseResponse($sResponse);

            $this->executeResponse($aResponse);
            $this->flushRequestSession();
        } catch (\Exception $e) {
            $this->mError = $e->getMessage();
            $this->flushRequestSession();

            return false;
        }

        return true;
    }

    // func

    /**
     * Сбрасывает данные по текущей сессии запроса.
     */
    protected function flushRequestSession()
    {
        $this->aFiles = [];
        $this->aHeader = [];
        $this->aActions = [];
        $this->aFilesPaths = [];
    }

    /**
     * @return null|string
     */
    public function getError()
    {
        return $this->mError;
    }

    /**
     * Задает url текущей площадки.
     *
     * @param $sClintHost
     *
     * @throws Exception
     */
    public function setClientHost($sClintHost)
    {
        if (!Validator::isUrl($sClintHost)) {
            throw new Exception("Not valid client host [{$sClintHost}]");
        }
        $this->sClientHost = $sClintHost;
    }

    /**
     * @param $aResponse
     * @return bool
     */
    protected function checkFormatResponse($aResponse)
    {
        if (empty($aResponse) || empty($aResponse['Header']) || empty($aResponse['Data'])) {
            return false;
        }

        if (!is_array($aResponse['Header'])) {
            return false;
        }

        return true;
    }
}// class
