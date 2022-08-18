<?php
/**
 * This is the template for generating list page file.
 *
 * @var \skewer\generators\page_module\Generator
 */
$className = $generator->moduleName;
echo '<?php';
?>

/**
 * @var array() $aListElement - массив элементов справочника
 * @var int $sectionId
 */
?>

<div>
    <?= '<?php '; ?> foreach ($aListElement as $aValue):?>
        <div class="b-dict-<?php echo '<?= '; ?>$aValue['id']; ?>">
            <a href="<?= '<?='; ?> $aValue['href']; ?>"><?= '<?= '; ?>$aValue['title']; ?> </a>
        </div>
    <?= '<?php '; ?> endforeach; ?>
    <?= '<?php '; ?> include(BUILDPATH . 'common/templates/paginator.php'); ?>
</div>
