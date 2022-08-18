<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 06.06.2016
 * Time: 15:54.
 */

namespace skewer\components\targets\types;

use skewer\base\ui;
use skewer\components\targets\models\Targets;

abstract class Prototype
{
    /**
     * @return string
     */
    abstract public function getType();

    /**
     * @param Targets $oTargetRow
     *
     * @return ui\builder\FormBuilder
     */
    abstract public function getFormBuilder($oTargetRow);

    /**
     * @param array $aData
     *
     * @return Targets
     */
    abstract public function getNewTargetRow($aData);

    /**
     * @param Targets $oTarget
     *
     * @return bool|string
     */
    public static function getTarget(Targets $oTarget)
    {
        $sData = \Yii::$app->getView()->renderFile(RELEASEPATH . 'components/targets/templates/' . $oTarget->type . '.php', $oTarget->getAttributes());

        return $sData;
    }

    /**
     * Отдает конфиг необходимых параметров.
     *
     * @return array
     */
    abstract public function getParams();

    /**
     * Сохраняет параметры.
     *
     * @param $aData
     */
    abstract public function setParams($aData);
}
