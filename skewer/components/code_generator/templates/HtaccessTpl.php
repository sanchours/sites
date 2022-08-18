<?php

namespace skewer\components\code_generator\templates;

use skewer\components\code_generator\TplInterface;
use skewer\components\code_generator\TplPrototype;

/**
 * @class skewer\components\code_generator\templates\HtaccessTpl
 *
 * @author ArmiT, $Author: armit $
 *
 * @version $Revision: 290 $
 * @date $Date: 2012-06-14 14:24:09 +0400 (Чт., 14 июня 2012) $
 * @project Skewer
 */
class HtaccessTpl extends TplPrototype implements TplInterface
{
    protected $sFilePath = '';

    protected $aData = [];

    protected $sTemplate = 'htaccess.twig';

    public function __construct($sFilePath, $aData, $sTemplate = false)
    {
        $this->sFilePath = $sFilePath;
        $this->aData = $aData;
        $this->sTemplate = (!$sTemplate) ? $this->sTemplate : $sTemplate;
    }

    // constructor

    public function make()
    {
//        return $this->createFileByTpl($this->getSiteRootPath().'web/'.$this->sFilePath, $this->sTemplate, $this->aData, false);
        return $this->createFileByTpl(WEBPATH . $this->sFilePath, $this->sTemplate, $this->aData, false);
    }

    public function remove()
    {
    }
}
