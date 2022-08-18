<?php
/**
 * @var array() $aListElement - массив элементов справочника
 * @var int $sectionId
 */
?>

<div>
    <?php  foreach ($aListElement as $aValue):?>
        <div class="b-dict-<?= $aValue['id']; ?>">
            <a href="<?= $aValue['href']; ?>"><?= $aValue['title']; ?> </a>
        </div>
    <?php  endforeach; ?>
    <?php  include(BUILDPATH . 'common/templates/paginator.php'); ?>
</div>
