<?php
/**
 * Шаблон для файл конфигукации списка редиректов.
 *
 * @var array набор переходов
 *   <старое значение> => <новое занчение>
 */
echo '<?php';
?>

return array(
<?php
    foreach ($aItems as $item) {
        echo sprintf(
            "    '%s' => '%s',\n",
            addslashes($item['old_url']),
            addslashes($item['new_url'])
        );
    }
?>
);

