<?php

use skewer\components\config\PatchPrototype;
use skewer\base\section\Parameters;

/**
 * Class Patch84723
 */
class Patch84723 extends PatchPrototype
{
    public $sDescription = 'Неиспользуемый параметр в CatalogViewer';

    public $bUpdateCache = false;

    /**
     * @return bool|void
     */
    public function execute()
    {
        Parameters::removeByName('viewCategory', 'content');
    }
}
