<?php

namespace skewer\base\ui;

use skewer\base\orm\ActiveRecord;
use yii\base\UserException;

/**
 * Класс для удобного формирования пользовательского сообщения об ошибке при сохранении orm\ActiveRecord
 * Класс самостоятельно оформляет приемлемое сообщение. На вход подается наследник orm\ActiveRecord.
 */
class ORMSaveException extends UserException
{
    public function __construct(ActiveRecord $oActiveRecord, $code = 0, \Exception $previous = null)
    {
        $aErrors = [];

        foreach ($oActiveRecord->getErrorList() as $sFieldName => $sErrorText) {
            $aErrors[] = $sErrorText;
        }

        $message = implode("<br />\n", $aErrors);

        parent::__construct($message, $code, $previous);
    }
}
