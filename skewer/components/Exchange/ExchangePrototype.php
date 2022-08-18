<?php

namespace skewer\components\Exchange;

use skewer\base\site_module\Request;
use skewer\base\SysVar;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use yii\web\ServerErrorHttpException;

/**
 * Class ExchangePrototype.
 *
 * @see http://v8.1c.ru/edi/edi_stnd/131/ - Протокол обмена между системой "1С:Предприятие" и сайтом
 */
class ExchangePrototype
{
    /** @var string Время начала обмена(!!Между итерациями передается через куки) */
    protected $sStartTime;

    /**
     * ExchangePrototype constructor.
     */
    public function __construct()
    {
        if ($sCookie = \Yii::$app->request->getHeaders()->get('cookie')) {
            $sHash = base64_decode(mb_substr($sCookie, mb_strlen('name=')));
            list(, $this->sStartTime) = StringHelper::explode($sHash, ':', true, true);
        }
    }

    /**
     * Вернёт экземпляр менеджера обмена данными с 1с
     *
     * @param $sType - тип, передаваемых данных
     *
     * @throws ServerErrorHttpException
     *
     * @return null|ExchangeGoods|ExchangeSales
     */
    public static function getInstance($sType)
    {
        $oInstance = null;

        switch ($sType) {
            case 'catalog':
                $oInstance = new ExchangeGoods();
                break;

            case 'sale':
                $oInstance = new ExchangeSales();
                break;

            default:
                throw new ServerErrorHttpException('Unsupported type');
        }

        return $oInstance;
    }

    /**
     * Запуска команду.
     *
     * @param $sCmd - имя комманды
     *
     * @return bool
     */
    public function executeCommand($sCmd)
    {
        $sMethod = 'cmd' . ucfirst($sCmd);

        if (method_exists($this, $sMethod)) {
            $this->{$sMethod}();

            return true;
        }

        return false;
    }

    /** Начало обмена с 1с */
    protected function cmdCheckauth()
    {
        $this->sendResponse(
            sprintf("%s\n%s\n%s", 'success', 'name', base64_encode(\Yii::$app->session->getId() . ':' . date('Y-m-d-H-i-s')))
        );
    }

    /** Инициализация параметров обмена */
    protected function cmdInit()
    {
        $this->sendResponse(
            sprintf(
                "zip=%s\nfile_limit=%d",
//                ArrayHelper::getValue(['no', 'yes'], (int)SysVar::get('1c.use_zip', false)),
                'no',
                SysVar::get('1c.file_limit', 51200)
            )
        );
    }

    /**
     * Приём файла импорта от 1с
     *
     * @return bool|string
     */
    protected function loadFileImport()
    {
        $sFileName = Request::getStr('filename');
        $sFullNameFile = self::getFullNameCurrentFile($sFileName);

        FileHelper::createDirectory(dirname($sFullNameFile));

        if (file_put_contents($sFullNameFile, \Yii::$app->request->getRawBody(), FILE_APPEND) === false) {
            return false;
        }

        return $sFullNameFile;
    }

    /**
     * Отправка ответа.
     *
     * @param $sContent - данные для ответа
     */
    protected function sendResponse($sContent)
    {
        $oResponse = \Yii::$app->getResponse();
        $oResponse->content = $sContent;
        $oResponse->send();
    }

    /**
     * Получить полное имя обрабатываемого файла.
     *
     * @param string $sFileName - имя файла
     *
     * @return string
     */
    protected function getFullNameCurrentFile($sFileName)
    {
        return $this->getDirCurrentExchange() . $sFileName;
    }

    /**
     * Вернет рабочую директорию.
     *
     * @return string
     */
    protected function getDirCurrentExchange()
    {
        return self::get1cDir() . "{$this->sStartTime}/";
    }

    /**
     * Директория хранения файлов 1с
     *
     * @return string
     */
    public static function get1cDir()
    {
        return ROOTPATH . 'import/1c/';
    }
}
