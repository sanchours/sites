<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 09.06.2016
 * Time: 11:00.
 */

namespace skewer\components\targets;

use yii\base\Event;

class CheckTarget extends Event
{
    private $aList = [];

    public $sName;

    public function addCheckTarget($sModuleName)
    {
        $this->aList[] = $sModuleName;
    }

    public function getList()
    {
        return $this->aList;
    }
}
