<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 21.08.13
 * Time: 15:49
 * To change this template use File | Settings | File Templates.
 */

namespace skewer\base\ft\proc\validator;

use skewer\base\ft;
use skewer\base\orm\Query;

/**
 * Валлидатор уникальности поля
 * Class Unique.
 */
class Unique extends Prototype
{
    /** имя параметра для задания набора полей */
    const FIELDS = 'fields';

    /**
     * ! системная функция
     * Проверяет правильность заполнения параметров и валидатора
     * Если есть ошибки - может выбросить исключение.
     *
     * @throws ft\exception\Model
     */
    public function checkInit()
    {
        foreach ($this->getFields() as $sName) {
            if (!isset($this->oRow->{$sName})) {
                throw new ft\exception\Model("Row does not contains field [{$sName}] (set in Unique validator)");
            }
            if (!$this->oModel->hasField($sName)) {
                throw new ft\exception\Model("Model does not contains field [{$sName}] (set in Unique validator)");
            }
        }
    }

    /**
     * Отдает набор полей.
     *
     * @return string[]
     */
    private function getFields()
    {
        // набор полей
        $aFields = $this->getParam(self::FIELDS);

        if ($aFields and !is_array($aFields)) {
            $aFields = ft\Fnc::toArray($aFields);
        }

        if (!$aFields) {
            $aFields = [$this->oField->getName()];
        }

        return $aFields;
    }

    /**
     * Проверяет данные на соответствие условиям
     *
     * @return bool
     */
    public function isValid()
    {
        // набор полей
        $aFields = $this->getFields();

        // проверка пустого значения
        $bEmpty = true;
        foreach ($aFields as $sName) {
            if (!empty($this->oRow->{$sName})) {
                $bEmpty = false;
            }
        }

        if ($bEmpty) {
            return true;
        }

        $oTable = Query::SelectFrom($this->oModel->getTableName(), $this->oModel->getName());

        // перебрать поля
        foreach ($aFields as $sName) {
            $oTable->where($sName, $this->oRow->{$sName});
        }

        // первичный ключ
        $sPK = $this->oModel->getPrimaryKey();

        // если задан - эту запись не учитывать
        if ($this->oRow->{$sPK}) {
            $oTable->where($sPK . '<>?', $this->oRow->{$sPK});
        }

        // выбрать
        $aFindItems = $oTable->getAll();

        return empty($aFindItems);
    }

    /**
     * Отдает текст ошибки.
     *
     * @return string
     */
    public function getErrorText()
    {
        return \Yii::t('ft', 'error_validator_unique');
    }
}
