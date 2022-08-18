<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 03.06.2016
 * Time: 16:10.
 */

namespace skewer\components\targets\types;

use skewer\components\targets\models\Targets;

class Google extends Prototype
{
    public function getType()
    {
        return 'google';
    }

    public function getFormBuilder($oTargetRow)
    {
    }

    public function getNewTargetRow($aData = [])
    {
        return Targets::getNewRow($aData, $this->getType());
    }

    public function getParams()
    {
        return [];
    }

    public function setParams($aData)
    {
    }
}
