<?php

    use skewer\base\section\Page;
    use skewer\base\section\Parameters;

    /*
     * @var $source string
     */

?>

<?php if (!Page::getVal(Parameters::settings, 'headPersonRightshow') && !empty($source['show_val'])):?>
    <div class="b-picbox">
        <?= $source['show_val']; ?>
    </div>
<?php endif; ?>