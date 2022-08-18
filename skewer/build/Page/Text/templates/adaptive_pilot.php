<?php

/**
 * @var string[] html текст c данными
 * @var string[] $adapt_class_suffix имя контейнера, пробрасывается в класс
 */

// если контента нет - блок не выводим
if (!isset($source)) {
    return '';
}

// имя кастомного класса
$sClassName = isset($adapt_class_suffix) ? 'sidebar__' . $adapt_class_suffix['value'] : '';

?>

<div class="sidebar__item <?= $sClassName; ?>">
      <?= $source['show_val']; ?>
</div>
