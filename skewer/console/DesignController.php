<?php

namespace app\skewer\console;

use skewer\components\design\model\Params as CssDataParams;

/**
 * Контроллер вспомогательных функций по дизайнерскому режиму.
 */
class DesignController extends Prototype
{
    /**
     * Формирует файл с модифицированными настройками после указанной даты.
     *
     * @param string $sDateTime дата [и время] модификации в формате 'YYYY-MM-DD HH:MM:SS' или 'YYYY-MM-DD'
     *
     * @return int
     */
    public function actionGetModified($sDateTime)
    {
        $this->br();

        $aList = CssDataParams::find()
            ->where(['>=', 'updated_at', $sDateTime])
            ->orderBy(['updated_at' => SORT_ASC])
            ->asArray()
            ->all();

        $aOut = [];
        $aUrls = [];

        foreach ($aList as $aRow) {
            $aOut[] = sprintf('%s;%s;%s', $aRow['layer'], $aRow['name'], $aRow['value']);
            if ($aRow['type'] == 'url' and $aRow['value'] !== 'empty' and $aRow['value']) {
                $aUrls[] = $aRow['value'];
            }
        }

        $sFileName = WEBPATH . 'files/upd_css_' . date('Y-m-d_H-i-s') . '.csv';

        if (!is_writable(WEBPATH . 'files/')) {
            return $this->showError("Не могу записать файл [{$sFileName}]");
        }

        $bRes = file_put_contents($sFileName, implode("\r\n", $aOut));

        if (!$bRes) {
            return $this->showError("Не удалось записать файл [{$sFileName}]");
        }

        $this->showText("Создан файл [{$sFileName}]");

        if ($aUrls) {
            $this->br();
            $this->showText('Список содержит файлы:');
            foreach ($aUrls as $sRow) {
                $this->showText('  ' . $sRow);
            }
        }

        $this->br();

        return 0;
    }

    /**
     * Применяет список изменений css из файла к сайту
     * Перед вызовом обязательно забэкапить хотябы базу.
     * После применения нужно будет сбросить дизайнерский ражим.
     *
     * @param string $sCssFileName имя csv файла с изменениями
     *
     * @return int
     */
    public function actionApplyCssUpdate($sCssFileName)
    {
        // относительный путь - попробовать найти во внутренних файлах
        if (mb_substr($sCssFileName, 0, 1) !== '/') {
            if (file_exists(ROOTPATH . $sCssFileName)) {
                $sCssFileName = ROOTPATH . $sCssFileName;
            } elseif (file_exists(WEBPATH . $sCssFileName)) {
                $sCssFileName = WEBPATH . $sCssFileName;
            }
        }

        if (!file_exists($sCssFileName)) {
            return $this->showError("Не могу найти файл [{$sCssFileName}]");
        }

        $this->showText('Список изменений:');

        $aOut = file($sCssFileName);
        foreach ($aOut as $sLine) {
            list($sLayer, $sName, $sValue) = explode(';', rtrim($sLine), 3);

            $aRow = CssDataParams::findOne(['layer' => $sLayer, 'name' => $sName]);

            if (!$aRow) {
                $this->showError("  Не найден параметр [{$sLayer}:{$sName}]");
                continue;
            }

            $aRow->value = $sValue;

            if ($aRow->save()) {
                $this->showText("  Изменено значение [{$sLayer}:{$sName}] на [{$sValue}]");
            } else {
                $this->showError("  Ошибка при изменении параметра [{$sLayer}:{$sName}]");
            }
        }

        $this->br();
        $this->showText('Для применения настроек нужно сбросить кэш сайта');

        return 0;
    }

    /**
     * Отдает набор дат изменений и количество модификаций в них.
     */
    public function actionGetCssHistory()
    {
        $h = \Yii::$app->db->createCommand(
            'SELECT COUNT(*) as `cnt`, DATE(`updated_at`) as `date`
            FROM css_data_params
            GROUP BY DATE(`updated_at`)
            ORDER BY `updated_at` DESC'
        )->query();

        $this->showText('Показывает даты изменений css и количество модификаций');
        $this->showText('     Дата    | Изменений');

        while ($aRow = $h->read()) {
            $this->showText(sprintf('  %s : %d', $aRow['date'], $aRow['cnt']));
        }
    }
}
