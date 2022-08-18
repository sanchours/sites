<?php

namespace skewer\components\design;

use skewer\base\SysVar;
use skewer\components\forms\entities\FieldEntity;
use yii\base\UserException;

/**
 * Прототип для переключателя форм
 */
abstract class TplSwitchForm extends TplSwitchPrototype
{
    /**
     * Отдает тип переключателя шаблонов.
     *
     * @return string
     */
    protected function getType()
    {
        return 'form';
    }

    /**
     * Отдает набор меток модулей, которые должны быть выведены в шапку.
     */
    protected function getModulesList()
    {
    }

    /**
     * Задать набор настроек для модулей.
     */
    public function setModuleSettings()
    {
    }

    /**
     * Установить типовой контент
     */
    public function setContent()
    {
    }

    /**
     * Установка типа поля.
     *
     * @param $sType - название поля {example: checkbox,checkboxGroup,radio..)
     * @param $sValue - значение id
     *
     * @return bool
     */
    protected function setFieldType($sType, $sValue)
    {
        return FieldEntity::updateAll(
            ['display_type' => $sValue],
            ['type' => $sType]
        );
    }

    /**
     * Установка расположения надписей для форм
     *
     * @param $sPosition
     *
     * @return bool
     */
    protected function setPositionLabel($sPosition)
    {
        $bReturn = FieldEntity::updateAll(['label_position' => $sPosition]);

        return $bReturn;
    }

    /**
     * Установка класса модификатора.
     *
     * @param $sClassName
     *
     * @return bool
     */
    public function setCommonClassForm($sClassName)
    {
        return SysVar::set('Forms.commonClass', $sClassName);
    }

    /**
     * Получение класса модификатора.
     *
     * @return bool
     */
    public static function getCommonClassForm()
    {
        return SysVar::get('Forms.commonClass');
    }

    /**
     * Установка класса для поля.
     *
     * @param $sType - название поля {example: checkbox,checkboxGroup,radio..)
     * @param $sClassName
     *
     * @return bool
     */
    public function setClassField($sType, $sClassName)
    {
        return FieldEntity::updateAll(
            ['class_modify' => $sClassName],
            ['type' => $sType]
        );
    }

    /**
     * Устанавливает параметр $sParamName для всех полей всех форм
     *
     * @param string $sParamName - имя параметра
     * @param mixed $mVal - значение параметра
     *
     * @throws UserException
     *
     * @return bool
     */
    protected function setParam4FieldForm($sParamName, $mVal)
    {
        $oModel = new FieldEntity();
        if (!isset($oModel->fields()[$sParamName])) {
            throw new UserException(sprintf('%s does not have column [%s]', FieldEntity::tableName(), $sParamName));
        }

        /** @var FieldEntity[] $aFieldRows */
        $aFieldRows = FieldEntity::find()->all();

        // Сохраняем старые значения параметров
        foreach ($aFieldRows as $oFieldRow) {
            /** @var BackupFormParams $oBackup */
            $oBackup = $this->oBackup;
            $oBackup->setParam4FieldForm($oFieldRow, $sParamName);
        }

        FieldEntity::updateAll([$sParamName => $mVal]);

        return true;
    }
}
