<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 08.06.2016
 * Time: 10:44.
 */

namespace skewer\components\targets\templates;

use skewer\components\targets\Yandex;

/* @var string $name */
?>

setTimeout(
    function() {
        if ( typeof yaCounter<?=Yandex::getCounter(); ?> !== 'undefined' ) {
            yaCounter<?=Yandex::getCounter(); ?>.reachGoal('<?=$name; ?>');
        }
    },1000
);
