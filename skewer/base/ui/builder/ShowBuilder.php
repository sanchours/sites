<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 28.02.2017
 * Time: 18:15.
 */

namespace skewer\base\ui\builder;

use skewer\components\ext\ShowView;

class ShowBuilder extends Prototype
{
    /** @var ShowView */
    protected $oForm;

    /**
     * Конструктор
     *
     * @param null $oInterface
     */
    public function __construct($oInterface = null)
    {
        if ($oInterface === null) {
            $this->oForm = new ShowView();
        } else {
            $this->oForm = $oInterface;
        }
    }
}
