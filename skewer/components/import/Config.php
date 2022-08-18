<?php

namespace skewer\components\import;

use skewer\components\import\ar\ImportTemplateRow;
use yii\helpers\ArrayHelper;

/**
 * Класс для работы с конфигурацией импорта.
 */
class Config
{
    /**
     * Хранилище данных.
     *
     * @var array
     */
    private $storage = [];

    /**
     * @param ImportTemplateRow $oTemplate
     *
     * @throws \Exception
     */
    public function __construct(ImportTemplateRow $oTemplate = null)
    {
        if ($oTemplate) {
            $aData = $oTemplate->getData();
            unset($aData['settings']);

            if ($oTemplate->settings) {
                $this->storage = json_decode($oTemplate->settings, true);
            } else {
                $this->storage = [];
            }

            if (!$this->storage) {
                $this->storage = [];
            }

            $this->storage = array_merge($this->storage, $aData);

            if (json_last_error()) {
                throw new \Exception('no valid data: ' . json_last_error_msg() . ' (' . json_last_error() . ')');
            }
        } else {
            $this->storage = [];
        }
    }

    /**
     * Установка данных.
     *
     * @param array $aData
     */
    public function setData($aData = [])
    {
        $this->storage = $aData;
    }

    /**
     * Возвращает значение параметра по имени.
     *
     * @param string $sParamName Путь к параметру
     * @param mixed $mDefault Значение по умолчанию
     *
     * @return mixed
     */
    public function getParam($sParamName, $mDefault = '')
    {
        return ArrayHelper::getValue($this->storage, $sParamName, $mDefault);
    }

    /**
     * Запись значения в конфиг.
     *
     * @param $sParamName
     * @param $mValue
     */
    public function setParam($sParamName, $mValue)
    {
        $this->storage[$sParamName] = $mValue;
    }

    /**
     * Возвращаем данные конфига в виде json.
     *
     * @return string
     */
    public function getJsonData()
    {
        return json_encode($this->storage);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->storage;
    }

    /**
     * Установка соответствий полей.
     *
     * @param array $aData
     *      [
     *          'field_11' => 5,
     *          'type_11' => 3,
     *      ]
     * @param bool $bRemoveNotExistsFields Удалить из настроек отсутствующие в $aData поля?
     */
    public function setFields($aData = [], $bRemoveNotExistsFields = false)
    {
        // Добавить новые поля в существующий шаблон импорта, не сбрасывая настройки существующих полей
        foreach ($aData as $sKey => $sVal) {
            if (mb_stripos($sKey, 'field_') === 0) {
                $iFieldId = mb_substr($sKey, 6);

                if (isset($aData["type_{$iFieldId}"]) and !isset($this->storage['fields'][$iFieldId])) {
                    $this->storage['fields'][$iFieldId] = [];
                }
            }
        }

        // Обновить значения полей / удалить неиспользуемые поля из шаблона импорта
        if (isset($this->storage['fields'])) {
            foreach ($this->storage['fields'] as $sKey => $sVal) {
                if (isset($aData['field_' . $sKey])) {
                    if (!isset($aData['type_' . $sKey])) {
                        continue;
                    }

                    $this->storage['fields'][$sKey]['name'] = $sKey;
                    $this->storage['fields'][$sKey]['importFields'] = $aData['field_' . $sKey];
                    $this->storage['fields'][$sKey]['type'] = $aData['type_' . $sKey];
                } elseif ($bRemoveNotExistsFields) {
                    unset($this->storage['fields'][$sKey]);
                }
            }
        }
    }

    /**
     * Установка настроек полей.
     *
     * @param array $aData
     */
    public function setFieldsParam($aData = [])
    {
        foreach ($aData as $sKey => $sValue) {
            if (preg_match('/^params_([^:]+):(.+)$/', $sKey, $aMatch)) {
                if (isset($this->storage['fields'][$aMatch[1]])) {
                    $this->storage['fields'][$aMatch[1]]['params'][$aMatch[2]] = $sValue;
                }
            }
        }
    }

    /**
     * Чистка данных по полям
     */
    public function clearFields()
    {
        unset($this->storage['fields']);
    }
}
