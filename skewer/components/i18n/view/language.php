<?php
/**
 * @var array
 */
?><?='<?php'; ?>

/**
 * Кэш языковых значений модулей
 * Файл генерируется автоматически.
 */

$aLang = array(
    <?php foreach ($values as $skey => $sValue): ?>
    '<?=$skey; ?>' => '<?=$sValue; ?>',
    <?php endforeach; ?>
);

return $aLang;