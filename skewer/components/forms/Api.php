<?php

namespace skewer\components\forms;

use skewer\base\section\Parameters;
use skewer\components\forms\entities\BuilderEntity;
use skewer\components\forms\forms\FormAggregate;

class Api
{
    public static $sRedirectUri = null;
    public static $sAnswerText = null;

    public static function getChildClassEntity(FormAggregate $formAggregate)
    {
        $sNameClass = $formAggregate->handler->value;

        if (
            $formAggregate->settings->system
            && $sNameClass
            && class_exists($sNameClass)
        ) {
            /** @var BuilderEntity $builderEntity */
            $builderEntity = new $sNameClass();
            if ($builderEntity instanceof BuilderEntity) {
                return $builderEntity;
            }
        }

        return '';
    }

    /**
     * Привязка формы к разделу.
     *
     * @param int $iFormId ид формы
     * @param int $iSectionId ид раздела
     * @param string $sGroup Группа параметров с объектом формы
     *
     * @return bool
     */
    public static function link2Section($iFormId, $iSectionId, $sGroup = 'forms')
    {
        return (bool) Parameters::setParams($iSectionId, $sGroup, 'FormId', $iFormId);
    }
}
