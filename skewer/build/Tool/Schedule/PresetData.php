<?php

$aLanguage = [];

$aLanguage['ru']['desc_time_settings'] = '
<style>

.b-schedule-table{}

.b-schedule-table td{
    padding: 5px;
    text-align: center;
    border: 1px solid gray;
}

.b-schedule-table td:first-child{
    text-align: left;
}
</style>

<table class="b-schedule-table" >
    <tr>
        <td>Пример конфигурации запуска</td>
        <td>Минута</td>
        <td>Час</td>
        <td>День</td>
        <td>Месяц</td>
        <td>День недели</td>
    </tr>
    <tr>
        <td>Запускать ежеминутно</td>
        <td>*</td>
        <td>*</td>
        <td>*</td>
        <td>*</td>
        <td>*</td>
    </tr>
    <tr>
        <td>Каждый день в 00:00</td>
        <td>0</td>
        <td>0</td>
        <td>*</td>
        <td>*</td>
        <td>*</td>
    </tr>
    <tr>
        <td>В 5:00 каждую неделю в пятницу</td>
        <td>0</td>
        <td>5</td>
        <td>*</td>
        <td>*</td>
        <td>5</td>
    </tr>
    <tr>
        <td>Каждую минуту c 00:00 по 00:59 еженедельно в воскресенье</td>
        <td>*</td>
        <td>0</td>
        <td>*</td>
        <td>*</td>
        <td>7</td>
    </tr>
    <tr>
        <td>В 3:20 каждую неделю в среду в мае</td>
        <td>20</td>
        <td>3</td>
        <td>*</td>
        <td>5</td>
        <td>3</td>
    </tr>
    <tr>
        <td>Каждые 10 минут с 1:00 до 1:59 ежедневно в апреле</td>
        <td>*/10</td>
        <td>1</td>
        <td>*</td>
        <td>4</td>
        <td>*</td>
    </tr>
</table>';

$aLanguage['en']['desc_time_settings'] = '
<style>
td {
    padding: 2px;
    text-align: center;
}
td:first-child {
    padding: 2px;
    text-align: left;
}
</style>
<table>
    <tr>
        <td>An example run configuration</td>
        <td>Min</td>
        <td>Hour</td>
        <td>Day</td>
        <td>Month</td>
        <td>Day of the week</td>
    </tr>
    <tr>
        <td>To run every minute</td>
        <td>*</td>
        <td>*</td>
        <td>*</td>
        <td>*</td>
        <td>*</td>
    </tr>
    <tr>
        <td>Every day at midnight</td>
        <td>0</td>
        <td>0</td>
        <td>*</td>
        <td>*</td>
        <td>*</td>
    </tr>
    <tr>
        <td>Every week on Friday at 5am</td>
        <td>0</td>
        <td>5</td>
        <td>*</td>
        <td>*</td>
        <td>5</td>
    </tr>
    <tr>
        <td>Every Sunday from 00:00 to 00:59</td>
        <td>*</td>
        <td>0</td>
        <td>*</td>
        <td>*</td>
        <td>7</td>
    </tr>
    <tr>
        <td>In May every Wednesday at 3:20am</td>
        <td>20</td>
        <td>3</td>
        <td>*</td>
        <td>5</td>
        <td>3</td>
    </tr>
    <tr>
        <td>Every day in April every 10 minutes from 1:00 to 1:59</td>
        <td>*/10</td>
        <td>1</td>
        <td>*</td>
        <td>4</td>
        <td>*</td>
    </tr>
</table>';

return $aLanguage;
