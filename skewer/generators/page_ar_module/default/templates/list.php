<?php
/**
 * This is the template for generating list page file.
 *
 * @var \yii\db\TableSchema
 */
$className = $generator->moduleName;
$languageCategory = mb_strtolower($generator->nameAR);
?>
{% if a<?php if (isset($modelName) && !empty($generator->aNameARs)):?><?=$modelName; ?><?php else: ?><?=$className; ?><?php endif; ?> is defined %}
<div>
    {% for iKey, aItem in a<?php if (isset($modelName) && !empty($generator->aNameARs)):?><?=$modelName; ?><?php else: ?><?=$className; ?><?php endif; ?> %}
    <div>
        <?php foreach ($descAR->columns as $key => $item): ?>
        <div>
            <?php if ($item->name == 'title'): ?>
                {{Lang.get('<?=$languageCategory; ?>.title_<?=$item->name; ?>')}} <a href="[{{sectionId}}][<?=$className; ?>?{{aItem.url}}]">{{aItem.<?=$item->name; ?>}}</a>
            <?php else: ?>
                {{Lang.get('<?=$languageCategory; ?>.title_<?=$item->name; ?>')}} <span>{{aItem.<?=$item->name; ?>}}</span>
            <?endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    {% endfor %}
</div>
{% endif %}


{# из skewer/build/common/templates #}
{% if showPagination %}
{% include "paginator.twig" %}
{% endif %}