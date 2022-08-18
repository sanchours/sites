<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 27.02.2017
 * Time: 18:53.
 */

namespace skewer\base\ui\builder;

use skewer\components\ext\UserFileView;

class FileBuilder extends Prototype
{
    /** @var UserFileView */
    protected $oForm;

    /**
     * Конструктор
     *
     * @param string $sLibName
     * @param null $oInterface
     */
    public function __construct($sLibName, $oInterface = null)
    {
        if ($oInterface === null) {
            $this->oForm = new UserFileView($sLibName);
        } else {
            $this->oForm = $oInterface;
        }
    }
}
