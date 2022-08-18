<?php

namespace skewer\base\site_module;

/**
 * Это временный  класс для хранения переменных среды.
 *
 * В дальнейшем должен быть переведен на один из стандартных механизмов
 * yii по передаче информации между модулями
 *
 * Каждый раз когда я пишу подобные комментарии модуль залипает пости
 * наметрво, так что если что не обессудье
 */
class Environment
{
    /**
     * данные.
     *
     * @var array
     */
    protected $aData = [];

    /**
     * Установка значения.
     *
     * @param string $sName
     * @param mixed $mValue
     */
    public function set($sName, $mValue)
    {
        $this->aData[$sName] = $mValue;
    }

    /**
     * Добавление значения, результирующий контейнер всегда будет массивом
     *
     * @param string $sName
     * @param mixed $mValue
     */
    public function add($sName, $mValue)
    {
        if (!isset($this->aData[$sName])) {
            $this->aData[$sName] = [];
        } elseif (!is_array($this->aData[$sName])) {
            $this->aData[$sName] = [$this->aData[$sName]];
        }
        $this->aData[$sName][] = $mValue;
    }

    /**
     * Запрос значения из хранилища
     * Если нет то вернет $mDefault, по умолчанию null.
     *
     * @param string $sName
     * @param null|mixed $mDefault
     *
     * @return mixed
     */
    public function get($sName, $mDefault = null)
    {
        return (isset($this->aData[$sName])) ? $this->aData[$sName] : $mDefault;
    }

    /**
     * Отдает все данные.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->aData;
    }

    /**
     * Очищаем параметры.
     */
    public function clear()
    {
        $this->aData = [];
    }
}
