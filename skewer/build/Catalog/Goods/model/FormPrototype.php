<?php

namespace skewer\build\Catalog\Goods\model;

use skewer\components\catalog\GoodsRow;
use yii\base\UserException;

/**
 * Прототип модели для построения формы редактиования товара
 * Class FormPrototype.
 */
abstract class FormPrototype
{
    /** @var array Входные данные */
    protected $data = [];

    /** @var string Тип источника входных данных */
    protected $type = '';

    /** @var string Имя поля, которое должно быть обновлено (при EditOnPlace редактировании) */
    protected $updField = '';

    /** @var \skewer\components\catalog\GoodsRow */
    protected $oGoodsRow;

    abstract public function load();

    abstract public function save();

    /**
     * Получение объекта GoodsRow для текущего товара.
     *
     * @return \skewer\components\catalog\GoodsRow
     */
    public function getGoodsRow()
    {
        return $this->oGoodsRow;
    }

    /**
     * Установка данных пришедших из JSON.
     *
     * @param array $aData Данные для обновления
     * @param string $sType Тип источника данных
     *
     * @return $this
     */
    public function setData($aData, $sType = '')
    {
        $this->data = $aData;
        $this->type = $sType;

        return $this;
    }

    /**
     * Получение входных данных.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Получение поля из входных данных.
     *
     * @param string $name Имя поля
     *
     * @return mixed
     */
    protected function getDataField($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : false;
    }

    /**
     * Задаем имя поля которое должн быть обновлено.
     *
     * @param $sFieldName
     *
     * @return $this
     */
    public function setUpdField($sFieldName)
    {
        $this->updField = $sFieldName;

        return $this;
    }

    /**
     * Вызывает прерывание выполнения и выдает ошибку.
     *
     * @param string $sMessage Текст сообщения об ошибки
     * @param int $iCode Код ошибки
     * @param \Exception $e
     *
     * @throws UserException
     */
    protected function riseError($sMessage, $iCode = 0, \Exception $e = null)
    {
        throw new UserException($sMessage, $iCode, $e);
    }

    /**
     * Форматированние сообщение об ошибке.
     *
     * @return bool|string
     */
    public function getErrorMsg()
    {
        if (!$this->oGoodsRow instanceof GoodsRow) {
            return \Yii::t('catalog', 'error_good_not_found');
        }

        $sErrorText = \Yii::t('catalog', 'error_in_validation');
        $aFieldList = $this->oGoodsRow->getFields();
        $aErrorList = $this->oGoodsRow->getErrorList();

        if (!$aErrorList) {
            return false;
        }

        foreach ($aErrorList as $sFieldName => $sFieldError) {
            if (isset($aFieldList[$sFieldName])) {
                $sFieldTitle = $aFieldList[$sFieldName]->getTitle();
            } else {
                $sFieldTitle = $sFieldName;
            }

            $sErrorText .= sprintf('<br /><i>%s</i> - %s', $sFieldTitle, $sFieldError);
        }

        return $sErrorText;
    }
}
