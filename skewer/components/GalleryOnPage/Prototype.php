<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 20.03.2017
 * Time: 11:56.
 */

namespace skewer\components\GalleryOnPage;

/**
 * Class Prototype.
 */
abstract class Prototype
{
    public $iCountItems = 3;

    /**
     * @return mixed
     */
    abstract public function getSettings();
}
