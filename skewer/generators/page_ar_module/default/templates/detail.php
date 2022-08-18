<?php
/**
 * This is the template for generating list page file.
 *
 * @var yii\db\TableSchema
 */
$className = $generator->moduleName;
$languageCategory = mb_strtolower($generator->nameAR);
//$descAR

?>
<div>
    {% if a<?php if (isset($modelName) && !empty($generator->aNameARs)):?><?=$modelName; ?><?php else: ?><?=$className; ?><?php endif; ?> is defined %}
    <?php if (!isset($modelName)): ?>{#{% include 'MicroData.twig' %}#}<?php endif; ?>
        <?php foreach ($descAR->columns as $key => $item): ?>

            <div>
                {{Lang.get('<?=$languageCategory; ?>.title_<?=$item->name; ?>')}} <span>{{a<?php if (isset($modelName) && !empty($generator->aNameARs)):?><?=$modelName; ?><?php else: ?><?=$className; ?><?php endif; ?>.<?=$item->name; ?>}}</span>
            </div>
        <?php endforeach; ?>

    {% endif %}
</div>