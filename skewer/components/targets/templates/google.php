<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 08.06.2016
 * Time: 10:44.
 */

/** @var string $category */
/** @var string $name */
?>

setTimeout(
    function() {
        if ( typeof gtag !== 'undefined' ) {

            gtag('event', '<?=$name; ?>', {
                'event_category' : '<?=$category; ?>',
            });

        } else if ( typeof ga !== 'undefined' ){
            ga('send', 'event', '<?=$category; ?>', '<?=$name; ?>');
        }
    },2000
);
