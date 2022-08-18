<?php

namespace skewer\components\code_generator\templates;

use skewer\components\code_generator\TplInterface;
use skewer\components\code_generator\TplPrototype;

/**
 * @class skewer\components\code_generator\templates\ConstantsTpl
 *
 * @author ArmiT, $Author: armit $
 *
 * @version $Revision: 290 $
 * @date $Date: 2012-06-14 14:24:09 +0400 (Чт., 14 июня 2012) $
 * @project Skewer
 */
class ConstantsTpl extends TplPrototype implements TplInterface
{
    protected $sFilePath = '';

    protected $aData = [];

    protected $sTemplate = 'constants.twig';

    public function __construct($sFilePath, $aData, $sTemplate = false)
    {
        $this->sFilePath = $sFilePath;
        $this->aData = $aData;
        $this->sTemplate = (!$sTemplate) ? $this->sTemplate : $sTemplate;
    }

    // constructor

    public function make()
    {
        return $this->createFileByTpl($this->getSiteRootPath() . $this->sFilePath, $this->sTemplate, $this->aData, false);
    }

    // func

    public function remove()
    {
    }

    // func
}// func
