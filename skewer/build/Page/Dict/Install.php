<?php

namespace skewer\build\Page\Dict;

use skewer\components\config\InstallPrototype;
use skewer\components\catalog\Dict;


/**
 * Class Install
 * @package skewer\build\Page\Dict */
class Install extends InstallPrototype {

    public function init() {
        return true;
    }

    public function install() {
        Dict::setBanDelDict('default');
        return true;
    }

    public function uninstall() {
        Dict::enableDelDict('default');
        return true;
    }

}
