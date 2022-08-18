<?php

namespace skewer\base\ui;

use yii\base\UserException;
use yii\db\ActiveRecord;

/**
 * Класс для удобного формирования пользовательского сообщения об ошибке при сохранении AR
 * Класс самостоятельно оформляет приемлемое сообщение. На вход подается наследник yii\db\ActiveRecord.
 */
class ARSaveException extends UserException
{
    public function __construct(ActiveRecord $oActiveRecord, $code = 0, \Exception $previous = null)
    {
        $aErrors = [];

        foreach ($oActiveRecord->getFirstErrors() as $sFieldName => $sErrorText) {
            $aErrors[] = $sErrorText;
        }

        $message = implode("<br />\n", $aErrors);

        parent::__construct($message, $code, $previous);
    }
}
