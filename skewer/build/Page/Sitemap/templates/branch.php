<?php
/**
 * Шаблон соответствует ветке дерева разделов
 * Рекурсивно парсится(аналог виджета).
 */

/** @var int $level */
/** @var array $aSections */
?>

<?php if ($aSections): ?>

    <li class="item-<?=$level; ?>">
        <?php if ($aSections['bActiveLink']): ?>
            <a href="<?=$aSections['href']; ?>"><?=$aSections['title']; ?></a>
        <?php else: ?>
            <?=$aSections['title']; ?>
        <?php endif; ?>
        <?php if (!empty($aSections['children'])): ?>
            <ul class="level-<?= $level + 1; ?>">
            <?php foreach ($aSections['children'] as $child):?>
                <?= Yii::$app->view->renderPhpFile(
    __FILE__,
    [
                        'aSections' => $child,
                        'level' => $level + 1,
                    ]
);
                ?>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </li>
<?php endif; ?>