<?php

namespace skewer\components\design;

/**
 * Прототип для хранения и восстановления параметров, измененных при смене шаблона.
 *
 * Позволяет вернуть назад все параметры, которые были изменены в ходе применения шаблона
 *
 * В идеале это позволит безболезненно переключаться между шаблонами, потому что выключение
 * шаблона вызовет полный откат на предыдущее состояние, а уже последующий запрос будет
 * включать новый шаблон со сборкой такого же файла
 */
abstract class BackupParamsPrototype
{
    /**
     * Контейнер данных, измененных автоматически.
     *
     * @var array
     */
    public $aData = [];

    /**
     * Контейнер данных, внесенных пользователем вручную.
     *
     * @var array
     */
    public $aUserData = [];

    public function __construct($config = [])
    {
        if (!empty($config)) {
            \Yii::configure($this, $config);
        }
    }

    /**
     * Сохраняет значение в массив данных, устанавливаемых вручную.
     *
     * @param string $sName
     * @param mixed $mVal
     */
    public function setUserData($sName, $mVal)
    {
        $this->aUserData[$sName] = $mVal;
    }

    /**
     * Запрашивает данные, установленные вручную.
     *
     * @param string $sName
     * @param null|mixed $mDefault
     *
     * @return null|mixed
     */
    public function getUserData($sName, $mDefault = null)
    {
        if (isset($this->aUserData[$sName])) {
            return $this->aUserData[$sName];
        }

        return $mDefault;
    }

    /**
     * Отдает строку данных для сохранения.
     */
    public function getDataForSaving()
    {
        $aData = [
            'aData' => $this->aData,
            'aUserData' => $this->aUserData,
        ];

        return json_encode($aData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    /**
     * Вернёт имя класса.
     *
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * Откатывает данные по внутреннему массиву.
     */
    abstract public function revertData();
}
