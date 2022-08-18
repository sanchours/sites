<?php if (!empty($items)):?>
    $(document).ready(function(){
        <?php foreach ($items as $key => $item):?>
            $('<?=$key; ?>').click(function(){
                <?php foreach ($item as $target):?>
                    <?=$target; ?>
                <?php endforeach; ?>
            });
        <?php endforeach; ?>
    });
<?php endif; ?>