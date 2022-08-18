<?php
/**
 * @var string
 */
?><?= '<?php'; ?>

use skewer\components\config\PatchPrototype;

class Patch<?= $number; ?> extends PatchPrototype {

    public $sDescription = '<?= $description; ?>';

    public $bUpdateCache = false;

    public function execute() {

    }

}