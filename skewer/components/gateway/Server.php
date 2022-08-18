<?php

namespace skewer\components\gateway;

use Exception;
use ReflectionClass;
use ReflectionMethod;
use skewer\base\log\Logger;

/**
 * Класс, отвечающий за прием удаленных обращений.
 */
class Server extends Prototype
{
    /**
     * @const int Тип валидации на выполнение - по принадлежности классу
     */
    const ValidateTypeConcreteClass = 0x03;

    /**
     * @const int Тип валидации на выполнение - по родительскому классу
     */
    const ValidateTypeParentClass = 0x04;

    /**
     * @const int Тип валидации на выполнение - по соответствию интерфейсу
     */
    const ValidateTypeInterface = 0x05;

    /**
     * @const float Дефолтное значение версии протокола
     */
    const PROTOCOL_VERSION_DEFAULT = '1.0';

    /**
     * Текущий тип валидации метода класса на выполнение.
     *
     * @var int
     */
    private $iValidateType = 0x04;

    /**
     * Значение валидации класса в зависимости от настроек.
     *
     * @var string
     */
    protected $sValidateValue = '';

    /**
     * Обработчик пришедших заголовков.
     *
     * @var null|callable|callback
     */
    protected $mHeaderHandler;

    /**
     * Массив ответов по результатам выполнения запросов имеет следующий вид:
     * ['response'] = <результат работы метода>
     * ['error']    = экземпляр, сгенерированного исключения.
     *
     * @var array
     */
    protected $aResponse = [];

    /** @var bool Флаг успешной расшифровки пришедшего пакета */
    private $bPackageDecrypted = false;

    /**
     * Флаг критической ошибки произошедшей на сайте, выкинет исключение на СМС
     *
     * @var bool
     */
    public static $bHaveCriticalError = false;

    /**
     * @param int $iStreamMode тип пакетов
     */
    public function __construct($iStreamMode = 0x00)
    {
        $this->iStreamType = (int) $iStreamMode;

        return true;
    }

    /**
     * Указывает класс, методы потомков которого можно выполнять средствами протокола.
     *
     * @param string $sClassName
     */
    public function addParentClass($sClassName)
    {
        $this->sValidateValue = $sClassName;
        $this->iValidateType = self::ValidateTypeParentClass;
    }

    // func

    /**
     * Указывает класс, методы которого можно выполнять средствами протокола.
     *
     * @param string $sClassName
     */
    public function addClass($sClassName)
    {
        $this->sValidateValue = $sClassName;
        $this->iValidateType = self::ValidateTypeConcreteClass;
    }

    // func

    /**
     * Указывает интерфейс к которому должны пренадлежать классы, методы которых можно выполнять.
     *
     * @param string $sInterfaceName
     */
    public function addInterface($sInterfaceName)
    {
        $this->sValidateValue = $sInterfaceName;
        $this->iValidateType = self::ValidateTypeInterface;
    }

    // func

    /**
     * Указыввает функцию обратного вызова для обработки пришедшего заголовка.
     *
     * @param callable|callback $mCalledMethod
     *
     * @return bool
     */
    public function onLoadHeaderHandler($mCalledMethod)
    {
        if (!is_callable($mCalledMethod)) {
            return false;
        }

        $this->mHeaderHandler = $mCalledMethod;

        return true;
    }

    // func

    /**
     * Проверяет наличие строки запроса в массиве POST и возвращает ее либо
     * генерирует исключение типа gateway\Exception.
     *
     * @throws Exception
     *
     * @return string
     */
    protected function getRequest()
    {
        if (!$_POST) {
            throw new Exception('Request error: Empty Request!');
        }
        if (empty($_POST['_gateway_request'])) {
            throw new Exception('Request error: Wrong Request!');
        }

        return $_POST['_gateway_request'];
    }

    // func

    protected function parseRequest($sRequest)
    {
        if (!function_exists('json_decode')) {
            throw new Exception('Request error: JSON library not found!');
        }
        $aPackage = json_decode(stripslashes($sRequest), true);

        if (empty($aPackage['Data']) || empty($aPackage['Header'])) {
            throw new Exception('Request error: Package has wrong format!');
        }
        $this->aHeader = $aPackage['Header'];

        if (empty($this->aHeader['Certificate']) || empty($this->aHeader['Client'])) {
            throw new Exception('Request error: Package has wrong Header (Not found required fields)!');
        }
        call_user_func_array($this->mHeaderHandler, [&$this, $this->aHeader]);

        $aPackage['Data'] = $this->decryptData($aPackage['Data'], $aPackage['Header']['crypted']);

        $this->bPackageDecrypted = true;

        if (!empty($aPackage['Data']['Actions'])) {
            $this->aActions = $aPackage['Data']['Actions'];
        }

        if (!empty($aPackage['Data']['Files'])) {
            $this->aFiles = $aPackage['Data']['Files'];
        }

        if (!$this->aActions && !$this->aFiles) {
            throw new Exception('Request error: No actions & Files!');
        }
    }

    // func

    /**
     * Проверяет правильность указания класса и метода для выполнения Согласно настройкам сервера. Их наличие и доступность.
     * Если метод является валидным с точки зрения настроек сервера, то происходит его выполнение.
     *
     * @param array|callable|callback $aAction Вызываемые методы
     *
     * @throws ExecuteException
     *
     * @return bool Возвращает true если метод разрешен для выполнения либо false в случае ошибки
     */
    protected function executeAction($aAction)
    {
        /* Есть ли в массиве не менее двух параметров */
        if (!is_array($aAction) or count($aAction) < 2) {
            return false;
        }

        /* Запись о классе есть и она не пустая */
        if (!isset($aAction['Class']) or empty($aAction['Class'])) {
            throw new ExecuteException('Error checking: Class [' . $aAction['Class'] . '] not received!');
        }
        $sClass = $aAction['Class'];

        if (preg_match('/^(\w+)(Page|Tool|Adm)(Service)?$/i', $sClass, $aMatch)) {
            $sName = $aMatch[1];
            $sLayer = $aMatch[2];

            $sClass = 'skewer\\build\\' . $sLayer . '\\' . $sName . '\\Service';
        }

        /* Запись о методе есть и она не пустая */
        if (!isset($aAction['Method']) or empty($aAction['Method'])) {
            throw new ExecuteException('Error checking: Method [' . $aAction['Method'] . '] not received!');
        }
        $sMethod = $aAction['Method'];

        $aParameters = [];

        /* Запись о параметрах есть и она не пустая */
        if (!empty($aAction['Parameters'])) {
            $aParameters = $aAction['Parameters'];
        }

        /* В зависимости от типа валидации */
        switch ($this->iValidateType) {
            /* Заглушка */
            case self::ValidateTypeConcreteClass:

                throw new ExecuteException('Error checking: Mode [concrete class] is not implemented!');
                break;

            /* По родительскому классу */
            case self::ValidateTypeParentClass:

                /* Пытаемся получить описание класса */
                $oCalledClass = new ReflectionClass($sClass);

                if (!($oCalledClass instanceof ReflectionClass)) {
                    throw new ExecuteException('Error checking: Class [' . $sClass . '] not found!');
                }
                /* Запрашиваем родителя класса и проверяем на соответствие условию */
                if ($oCalledClass->getParentClass()->name != $this->sValidateValue) {
                    throw new ExecuteException('Security error: Class [' . $sClass . '] not accessible!');
                }
                break;

            /* Заглушка */
            case self::ValidateTypeInterface:

                throw new ExecuteException('Error checking: Mode [Interface] is not implemented!');
                break;
        }// case of validate type

        /* Проверяем наличие метода в классе */

        /* Пытаемся получить описание метода */
        $oCalledMethod = new ReflectionMethod($sClass, $sMethod);

        /* Метод найден */
        if (!($oCalledMethod instanceof ReflectionMethod)) {
            throw new ExecuteException('Error checking: Method [' . $sMethod . '] in class [' . $sClass . '] not found!');
        }
        /* И он публичный */
        if (!$oCalledMethod->isPublic()) {
            throw new ExecuteException('Error checking: Method [' . $sMethod . '] in class [' . $sClass . '] not accessible!');
        }
        /* Пытаемся выполнить */
        $mResponse = $oCalledMethod->invokeArgs(new $sClass(), $aParameters);

        return $mResponse;
    }

    // func

    /**
     * Последовательно выполнить все методы по запросу.
     *
     * @throws Exception
     *
     * @return bool
     */
    protected function doActions()
    {
        if (!$this->aActions) {
            throw new Exception('Execute error: Queue of actions is empty!');
        }
        foreach ($this->aActions as $aAction) {
            try {
                $aResponse = [
                    'response' => $this->executeAction($aAction),
                    'error' => null,
                ];
                $this->aResponse[] = $aResponse;
            } catch (ExecuteException $e) { /* До выполнения дело не дошло, отвалились по недопустимости вызова */
                $this->aResponse[] = [
                    'response' => null,
                    'error' => $e->getMessage(),
                ];

                /*Передадим флаг критичности ошибки*/
                $this->aResponse[0]['critical_error'] = (int) self::$bHaveCriticalError;
            }
        }// each method

        return true;
    }

    // func

    /**
     * Должен обрабатывать действия после загрузки файлов.
     *
     * @return bool
     */
    protected function doFiles()
    {
        return (bool)$this->aFiles;
    }

    // func

    /**
     * Собирает ответ с результатами обработки запроса. Шифорвание происходит в зависимости от режима.
     *
     * @param array $aHeader Массив заголовков, отправляемых в ответе
     * @param array $aResponse Массив ответов отработки запроса
     *
     * @throws Exception
     *
     * @return string Возвращает строку ответа либо генерирует исключение
     * типа gateway\Exception в случае ошибки
     */
    protected function doResponse($aHeader, $aResponse)
    {
        $aPackage['Header'] = $aHeader;

        $aResponse = json_encode($aResponse);
        $sClientHost = isset($this->aHeader['Client']) ? $this->aHeader['Client'] : '';
        $aPackage['Header']['Certificate'] = $this->makeCertificate($sClientHost, $aResponse);

        if ($this->packageIsDecrypted()) {
            $aResponse = $this->encryptData($aResponse, $aPackage['Header']['crypted']);
        }

        if (empty($aResponse)) {
            throw new Exception('Response error: Assembled response is empty!');
        }
        $aPackage['Data'] = $aResponse;

        return json_encode($aPackage);
    }

    // func

    /**
     *  Слушаем на предмет запросов, выполняем, собираем ответ, отдаем
     *
     * @throws Exception
     */
    public function handler()
    {
        try {
            $sRequest = $this->getRequest();
            $this->parseRequest($sRequest);

            $this->doFiles();
            $this->doActions();

            $aHeader = [
                'Status' => 200,
                'Version' => $this->aHeader['Version'],
            ];

            echo $this->doResponse($aHeader, $this->aResponse);
        } catch (Exception $e) {
            Logger::dumpException($e);

            $aHeader = [
                'Status' => 500,
                'Version' => isset($this->aHeader['Version'])
                    ? $this->aHeader['Version']
                    : Server::PROTOCOL_VERSION_DEFAULT,
            ];

            $aErrorResponse = ['error' => $e->getMessage()];
            echo $this->doResponse($aHeader, $aErrorResponse);
        }
    }

    // func

    /**
     * Отдает флаг успешной расшифровки пришедшего пакета.
     *
     * @return bool
     */
    public function packageIsDecrypted()
    {
        return $this->bPackageDecrypted;
    }
}// class
