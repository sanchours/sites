<?php
/** @var array $aUpdated - обновленные параметры */

/* @var array $aSkipped - пропущенные параметры */
?>
<style>

    table{
        width: 100%;
        border: 1px solid black;
    }
    td {
        border: 1px solid black;
        padding: 3px 10px;
    }

    table.skipped_table td{
        width: 30%;
    }

    table.updated_table td{
        width: 45%;
    }

    .skipped_title{

        padding: 5px 10px;
        background: #FF4E38;
        border-left: 5px solid #B93D30;
    }

    .skipped_updated{
        padding: 5px 10px;
        background: #0FC872;
        border-left: 5px solid #0f9d58;
    }

    div.container{
        height: 730px;
        overflow: auto;
    }

    span.bolder{
        font-weight: 700;
    }

</style>

<div class="container">

    <h1>Список изменений:</h1>

    <?php if ($aSkipped): ?>
        <h2 class="skipped_title">Пропущенные параметры</h2>

        <table class="skipped_table">
            <tr style="font-weight: 700;">
                <td>Причина</td>
                <td>Название параметра</td>
                <td>Значение</td>
            </tr>
            <?foreach ($aSkipped as $item): ?>
                <tr>
                    <td><?=$item['cause']; ?></td>
                    <td><?=$item['title']; ?></td>
                    <td><?=$item['value']; ?></td>
                </tr>

            <?php endforeach; ?>

        </table>

    <div>
        <span class="bolder">Описание ошибок:</span><br>
        <span class="bolder">Не удалось сохранить файл:</span> - скорее всего, вы выполняете перенос на площадку,  на которой не активирован один из элементов дизайна (шапка, футер и т.п.)<br>
        <span class="bolder">Не удалось загрузить файл</span> - либо загружаемый файл не существует, либо он располагается в директории ассета(файлы ассетов не переносятся)<br>
        <span class="bolder">Не найден параметр</span> - импортируемый параметр не существует на конечной площадке.<br>
    </div>

    <?php endif; ?>


    <?php if ($aUpdated): ?>
        <h2 class="skipped_updated">Обновлённые параметры</h2>


        <table class="updated_table">
            <tr style="font-weight: 700;">
                <td>Название параметра</td>
                <td>Новое значение</td>
            </tr>
            <?foreach ($aUpdated as $item): ?>
                <tr>
                    <td><?=$item['title']; ?></td>
                    <td><?=$item['value']; ?></td>
                </tr>

            <?php endforeach; ?>

        </table>


    <?php endif; ?>
</div>



