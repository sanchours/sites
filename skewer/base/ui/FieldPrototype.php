<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 16.10.13
 * Time: 10:27
 * To change this template use File | Settings | File Templates.
 */

namespace skewer\base\ui;

use yii\web\ServerErrorHttpException;

/**
 * Прототип поля интерфейса
 * Class FieldPrototype.
 */
abstract class FieldPrototype
{
    /** @var string имя поля */
    protected $sName = '';

    /** @var string название поля */
    protected $sTitle = '';

    /** @var array список параметров для работы */
    protected $aParams = [];

    /** @var array параметры для отдачи в интерфейс */
    protected $aOutParams = [];

    /**
     * Отдает имя.
     *
     * @return string
     */
    public function getName()
    {
        return $this->sName;
    }

    /**
     * Задает имя.
     *
     * @param string $sName
     */
    public function setName($sName)
    {
        $this->sName = $sName;
    }

    /**
     * Отдает название поля.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->sTitle;
    }

    /**
     * Задает название поля.
     *
     * @param string $sTitle
     */
    public function setTitle($sTitle)
    {
        $this->sTitle = $sTitle;
    }

    /**
     * Отдает параметр по имени
     * Используется для внутренней организации работы
     * Для задания выходных значений используются методы getOutParam, setOutParam.
     *
     * @param string $sName
     *
     * @return null|mixed
     */
    public function getParam($sName)
    {
        return isset($this->aParams[$sName]) ? $this->aParams[$sName] : null;
    }

    /**
     * Задает параметр
     * Используется для внутренней организации работы
     * Для задания выходных значений используются методы getOutParam, setOutParam.
     *
     * @param string $sName
     * @param mixed $mVal
     */
    public function setParam($sName, $mVal)
    {
        $this->aParams[$sName] = $mVal;
    }

    /**
     * Задает список параметров.
     *
     * @param array $aParams
     */
    public function setParamList($aParams)
    {
        $this->aParams = $aParams;
    }

    /**
     * Отдает список выходных параметров
     * Используется для построения интерфейса
     * Для задания параметров работы используются методы getParam, setParam.
     *
     * @throws ServerErrorHttpException
     *
     * @return array
     */
    public function getOutParamList()
    {
        if (!is_array($this->aOutParams)) {
            throw new ServerErrorHttpException('Выходные данные не являются массивом');
        }

        return array_merge($this->getBaseOutParams(), $this->aOutParams);
    }

    /**
     * Задает список параметров вывода
     * Используется для построения интерфейса
     * Для задания параметров работы используются setParamList.
     *
     * @param array $aParams
     */
    public function setOutParamList($aParams)
    {
        $this->aOutParams = $aParams;
    }

    /**
     * Метод для вывода базового набора конфигурационных параметров.
     *
     * @return array
     */
    abstract protected function getBaseOutParams();

    /**
     * Отдает выходной параметр
     * Используется для построения интерфейса
     * Для задания параметров работы используются методы getParam, setParam.
     *
     * @param $sName
     *
     * @return null|mixed
     */
    public function getOutParam($sName)
    {
        return isset($this->aOutParams[$sName]) ? $this->aOutParams[$sName] : null;
    }

    /**
     * Задает выходной параметр
     * Используется для построения интерфейса.
     *
     * @param $sName
     * @param $mVal
     */
    public function setOutParam($sName, $mVal)
    {
        $this->aOutParams[$sName] = $mVal;
    }
}
