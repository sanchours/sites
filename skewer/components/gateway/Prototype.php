<?php

namespace skewer\components\gateway;

/**
 * Прототип для клиента и сервера удаленного соединения.
 */
class Prototype
{
    /**
     * @const int Только нешфрованные запросы
     */
    const StreamTypeNEncrypt = 0x00;

    /**
     * @const int Только шифрованные запросы
     */
    const StreamTypeEncrypt = 0x01;

    /**
     * @const int Автоопределение
     */
    const StreamTypeAuto = 0x02;

    /**
     * Режим работы с потоком
     *
     * @var int
     */
    protected $iStreamType = 0x00;

    /**
     * Ключ для подписи и шифрования пакетов.
     *
     * @var string
     */
    protected $sKey = '';

    /**
     * Массив с параметрами заголовка отправляемого вместе с запросом
     *
     * @var array
     */
    protected $aHeader = [];

    /**
     * Список методов запрашиваемых на выполнение в рамках одного запроса.
     *
     * @var array
     */
    protected $aActions = [];

    /**
     * Спиcок записей на отправку файлов одним запросом на сервер
     *
     * @var array
     */
    protected $aFiles = [];

    /**
     * Метод, метод вызываемый для шифрования пакета. Принимает два параметра: ключ для шифрования и текст пакета.
     *
     * @var callback
     */
    protected $aEncryptCallback;

    /**
     * Метод, метод вызываемый для дешифровки пакета. Принимает два параметра: ключ для шифрования и текст пакета.
     *
     * @var callback
     */
    protected $aDecryptCallback;

    /**
     * Указывает ключ шифрования для режима с внутренним шифрованием
     *
     * @param $sKey
     */
    public function setKey($sKey)
    {
        $this->sKey = $sKey;
    }

    // func

    /**
     * Позволяет указать метод, который будет производить шифрование пакета запроса. Метод принимает два параметра:
     * 1. Ключ для шифрования
     * 2. Текст пакета
     * 3. В качестве результата должен возвращать зашифрованный текст запроса.
     *
     * @param callable|callback $aCalledMethod Вызываемый метод
     *
     * @throws Exception
     */
    public function onEncrypt($aCalledMethod)
    {
        if (!is_callable($aCalledMethod)) {
            throw new Exception('doEncrypt error: is not callable method');
        }
        $this->aEncryptCallback = $aCalledMethod;
    }

    // func

    /**
     * Позволяет указать метод, который будет производить дешифровку пакета ответа. Метод принимает два параметра:
     * 1. Ключ для расшифовки
     * 2. Текст пакета ответа
     * 3. В качестве результата должен возвращать зашифрованный текст ответа.
     *
     * @param callable|callback $aCalledMethod Вызываемый метод
     *
     * @throws Exception
     */
    public function onDecrypt($aCalledMethod)
    {
        if (!is_callable($aCalledMethod)) {
            throw new Exception('doDecrypt error: is not callable method');
        }
        $this->aDecryptCallback = $aCalledMethod;
    }

    // func

    /**
     * Запускает пользовательскую функцию шифрования.
     *
     * @param string $sData текст ответа
     *
     * @return bool|string Возвращает зашифрованный текст ответа либо false
     */
    protected function doEncrypt($sData)
    {
        return call_user_func_array($this->aEncryptCallback, [$sData, $this->sKey]);
    }

    // func

    /**
     * Запускает пользовательскую функцию расшифровки.
     *
     * @param string $sData Зашифрованный текст запроса
     *
     * @return bool|string Возвращает расшифрованный текст запроса либо false
     */
    protected function doDecrypt($sData)
    {
        return call_user_func_array($this->aDecryptCallback, [$sData, $this->sKey]);
    }

    // func

    /**
     * Генерируем подпись для запроса.
     *
     * @param string $sClientHost Домен, с которого пришел запрос
     * @param string $sBody ело запроса
     *
     * @return string
     */
    protected function makeCertificate($sClientHost, $sBody)
    {
        return md5($this->sKey . $sClientHost . $sBody);
    }

    // func

    /**
     * Шифрует пакет данных в зависимости от настроек.
     *
     * @param string $sData Строка данных
     * @param bool $bIsCrypted Переменная в которой взводится либо опускается флаг шифрования
     *
     * @throws Exception
     *
     * @return bool|string Возвращает зашифрованный согласно режиму пакет данных либо
     */
    protected function encryptData($sData, &$bIsCrypted = false)
    {
        switch ($this->iStreamType) {
            /* Не шифруем пакет */
            case self::StreamTypeNEncrypt:

                $bIsCrypted = false;
                break;

            /* Шифруем пакет */
            case self::StreamTypeEncrypt:

                if (!($this->sKey)) {
                    throw new Exception('Encrypt error: Key not found!');
                }
                $sData = $this->doEncrypt($sData);

                if (!$sData) {
                    throw new Exception('Encrypt error: Callback function is not defined or not valid!');
                }
                $sData = base64_encode($sData);
                $bIsCrypted = true;

                break;
        }// stream mode

        return $sData;
    }

    //func

    /**
     * Расшифровывает пакет согласно установкам сервера.
     *
     * @param string $sData зашифрованный текст пакета
     * @param bool $bIsCrypted Заголовок пакета указатель на шифрование
     *
     * @throws Exception
     *
     * @return null|array Возвращает расшифрованный массив данных либо null
     */
    protected function decryptData($sData, $bIsCrypted = false)
    {
        $aData = null;

        switch ($this->iStreamType) {
            case self::StreamTypeEncrypt:

                if (!$bIsCrypted) {
                    throw new Exception('Decrypt error: Wrong settings! Do not obtained Crypt flag!');
                }
                $sData = base64_decode($sData);
                if (empty($sData)) {
                    throw new Exception('Decrypt error: Request has invalid format');
                }
                if (!($this->sKey)) {
                    throw new Exception('Decrypt error: Key not found!');
                }
                $sData = $this->doDecrypt($sData);
                if (empty($sData)) {
                    throw new Exception('Decrypt error: Callback function is not defined or not valid!');
                }
                $aData = json_decode($sData, true);

                if (!is_array($aData)) {
                    throw new Exception('Decrypt error: Package not decrypted');
                }
                break;

            case self::StreamTypeNEncrypt:

                // нет данных на входе либо не доходит
                if ($bIsCrypted) {
                    throw new Exception('Decrypt error: Wrong settings! Obtained Crypt flag!');
                }
                $aData = json_decode($sData, true);
                break;
        }// Stream mode

        return $aData;
    }

    // func
}// class
