<?php
/**
 * @author Артем
 * @date 27.01.14
 * @project canape
 */

namespace skewer\components\config\installer;

use skewer\base\command;

abstract class Action extends command\Action
{
    protected $module;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }
}
