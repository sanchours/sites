<?php

namespace skewer\base\site_module;

/**
 * Временныый класс для работы с ответами в формате JSON
 * Используется в админке и диз режиме
 * Создан для разбора глобального класса процессора.
 */
class JsonResponse
{
    /**
     * Содержит массив ответов, полученных от модулей.
     *
     * @var array
     */
    protected $aJSONResponse = [];

    /**
     * Содержит массив заголовков JSON-запроса.
     *
     * @var array
     */
    protected $aJSONHeaders = [];

    /**
     * Добавляет JSON ответ от модуля во внутреннее хранилище для дальнейшей упаковки.
     *
     * @param Context $oContext Контекст отработанного модуля
     *
     * @return bool
     */
    public function addJSONResponse(Context $oContext)
    {
        $aModuleResponse = $oContext->getJSONHeadersList();

        $aModuleResponse['path'] = $oContext->sLabelPath;
        $aModuleResponse['params'] = $oContext->getData();
        $aModuleResponse['className'] = $oContext->sClassName;
        $aModuleResponse['moduleName'] = $oContext->getModuleName();
        $aModuleResponse['moduleDir'] = $oContext->getModuleWebDir();
        $aModuleResponse['moduleLayer'] = $oContext->getModuleLayer();

        $aModuleResponse['cmd'] = (isset($aModuleResponse['params']['cmd']) and $aModuleResponse['params']['cmd']) ? $aModuleResponse['params']['cmd'] : false;

        $this->aJSONResponse['data'][] = $aModuleResponse;

        return true;
    }

    // func

    /**
     * Добавляет значение в корень посылки.
     *
     * @param string $sName
     * @param mixed $mValue
     */
    public function addJSONResponseRootValue($sName, $mValue)
    {
        $this->aJSONResponse[$sName] = $mValue;
    }

    /**
     * Отдает JSON посылку.
     *
     * @return array
     */
    public function getJSONResponse()
    {
        return $this->aJSONResponse;
    }

    /**
     * Добавляет Идентификатор сессии в JSON ответ
     *
     * @param string $sSessionId хеш-ключ сессии
     *
     * @return bool
     */
    public function addSessionId($sSessionId)
    {
        $this->aJSONResponse['sessionId'] = $sSessionId;

        return true;
    }

    // func

    /**
     * Добавяляет статус и сообщение отработки JSON пакета.
     *
     * @param string $sMessage Сообщение отработки пакета
     * @param bool $bError Отработано с ошибкой либо нет
     *
     * @return bool
     */
    public function addResponseStatus($sMessage, $bError = false)
    {
        $this->aJSONResponse['message'] = $sMessage;
        $this->aJSONResponse['success'] = $bError;

        return true;
    }

    // func

    /**
     * Устанавливает заголовки JSON запроса.
     *
     * @param array $aJSONHeaders массив заголовков
     *
     * @return array
     */
    public function setJSONHeaders($aJSONHeaders)
    {
        return $this->aJSONHeaders = $aJSONHeaders;
    }

    // func

    /**
     * Возвращает массив заголовков JSON запроса.
     *
     * @return array
     */
    public function getJSONHeaders()
    {
        return $this->aJSONHeaders;
    }
}
