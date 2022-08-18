<?php

namespace skewer\build\Design\CSSTransfer;

use skewer\base\SysVar;
use skewer\build\Design\CSSEditor\models\CssFiles;
use skewer\components\design\model\Params;
use yii\base\Exception;

/**
 * Class Api.
 */
class Api
{
    /**
     * Формирует файл с модифицированными настройками после указанной даты.
     *
     * @param string $sDateTime дата [и время] модификации в формате 'YYYY-MM-DD HH:MM:SS' или 'YYYY-MM-DD'
     * @param $sDateTime
     * @param mixed $sDateTimeEnd
     *
     * @throws Exception
     *
     * @return string
     */
    public static function getModified($sDateTime, $sDateTimeEnd)
    {
        // $this->br();
        $sText = '';

        $aList = Params::find()
            ->where(['between', 'updated_at', $sDateTime, $sDateTimeEnd])
            ->orderBy(['updated_at' => SORT_ASC])
            ->asArray()
            ->all();

        $aOut = [];
        $aUrls = [];

        if (!empty($aList)) {
            foreach ($aList as $aRow) {
                $aOut[] = sprintf('%s;%s;%s', $aRow['layer'], $aRow['name'], $aRow['value']);
                if ($aRow['type'] == 'url' and $aRow['value'] !== 'empty' and $aRow['value']) {
                    $aUrls[] = $aRow['value'];
                }
            }
        }

        $sDate = date('Y-m-d_H-i');

        $sWebFilePath = WEBPROTOCOL . WEBROOTPATH . 'files/design/out/upd_css_' . $sDate . '.csv';

        $sFileName = WEBPATH . 'files/design/out/upd_css_' . $sDate . '.csv';

        if (!file_exists(WEBPATH . 'files/design/out/')) {
            mkdir(WEBPATH . 'files/design/out/', 0775);
        }

        if (!is_writable(WEBPATH . 'files/')) {
            throw new \Exception("Не могу записать файл [{$sFileName}]");
        }
        if (empty($aOut)) {
            throw new \Exception('Изменения не найдены!');
        }
        $handle = fopen($sFileName, 'a');

        foreach ($aOut as $value) {
            $bRes = fputcsv($handle, explode(';', $value), ';');
        }
        fclose($handle);

        if (!$bRes) {
            throw new \Exception("Не удалось записать файл [{$sFileName}]");
        }
        $sText .= "\r\n";
        $sText .= "Создан файл {$sWebFilePath}";

        $sText .= "\r\n";

        return $sText;
    }

    /**
     * Применяет список изменений css из файла к сайту
     * Перед вызовом обязательно забэкапить хотябы базу.
     * После применения нужно будет сбросить дизайнерский ражим.
     *
     * @param $sCssFileName
     * @param mixed $sTestSiteUrl
     *
     * @throws Exception
     *
     * @return string
     */
    public static function applyCssUpdate($sCssFileName, $sTestSiteUrl)
    {
        $bOverlayFiles = (bool) SysVar::get('CSSTransfer.OverlayValues');

        if (!file_exists($sCssFileName)) {
            throw new \Exception("Не могу найти файл [{$sCssFileName}]");
        }
        $aOut = file($sCssFileName);

        $aDataReports = [
            'aUpdated' => '',
            'aSkipped' => '',
        ];

        foreach ($aOut as $sLine) {
            $sCurLine = rtrim($sLine);

            if (!$sCurLine) {
                continue;
            }

            list($sLayer, $sName, $sValue) = explode(';', rtrim($sLine), 3);

            $aRow = Params::findOne(['layer' => $sLayer, 'name' => $sName]);

            if (!$aRow) {
                $aDataReports['aSkipped'][] = [
                    'title' => "{$sLayer}:{$sName}",
                    'value' => '',
                    'cause' => 'Не найден параметр',
                ];

                continue;
            }

            $sValue = str_replace('"', '', $sValue);

            if ($aRow['type'] == 'url' and $sValue !== 'empty' and $sValue) {
                /*Похоже в качестве параметра используется файл*/
                /*Необходимо его подтянуть*/

                /*Хак чтобы подгрузить изображения из ../images*/
                $sValue = str_replace('..', '', $sValue);

                $aAvailableCodes = ['200', '301', '302', '304'];

                if (in_array(self::getHTTPCode('http://' . $sTestSiteUrl . $sValue), $aAvailableCodes) === false) {
                    $sValue = str_replace('/images', '', $sValue);
                }

                $sTargetPath = str_replace('//', '/', WEBPATH . $sValue);

                $sFileContent = @file_get_contents('http://' . $sTestSiteUrl . $sValue);

                if ($sFileContent === false) {
                    $aDataReports['aSkipped'][] = [
                        'title' => "{$sLayer}:{$sName}",
                        'value' => "1. /images{$sValue}<br>2. {$sValue}",
                        'cause' => 'Не удалось загрузить файл',
                    ];

                    continue;
                }

                if (!file_exists($sTargetPath) || $bOverlayFiles) {
                    if (@file_put_contents($sTargetPath, $sFileContent) === false) {
                        $aDataReports['aSkipped'][] = [
                            'title' => "{$sLayer}:{$sName}",
                            'value' => "{$sTargetPath}",
                            'cause' => 'Не удалось загрузить файл',
                        ];

                        continue;
                    }
                }
            }

            $aRow->value = $sValue;

            if ($aRow->save()) {
                $aDataReports['aUpdated'][] = [
                    'title' => "{$sLayer}:{$sName}",
                    'value' => $sValue,
                ];
            } else {
                $aDataReports['aSkipped'][] = [
                    'title' => "{$sLayer}:{$sName}",
                    'value' => $sValue,
                    'cause' => 'Ошибка при изменении параметра',
                ];
            }
        }

        \skewer\build\Tool\Utils\Api::dropCache();

        return \Yii::$app->view->renderPhpFile(__DIR__ . '/templates/report.php', $aDataReports);
    }

    /**
     * Отдает набор дат изменений и количество модификаций в них.
     */
    public static function getCssHistory()
    {
        $sText = '';

        $h = \Yii::$app->db->createCommand(
            'SELECT COUNT(*) as `cnt`, DATE(`updated_at`) as `date`
            FROM css_data_params
            GROUP BY DATE(`updated_at`)
            ORDER BY `updated_at` DESC'
        )->query();

        $sText .= "Показывает даты изменений css и количество модификаций \r\n";
        $sText .= "  Дата           | Изменений\r\n";

        while ($aRow = $h->read()) {
            $sText .= sprintf("  %s : %d\r\n", $aRow['date'], $aRow['cnt']);
        }

        return $sText;
    }

    public static function getHTTPCode($url)
    {
        $headers = get_headers($url);

        return mb_substr($headers[0], 9, 3);
    }

    public static function addCSSBlock($sContent)
    {
        $aContent = json_decode($sContent, true);

        if ($aContent === null) {
            throw new \Exception('Не могу разобрать файл');
        }
        $oCssFile = new CssFiles();

        unset($aContent['id']);

        $oCssFile->setAttributes($aContent);

        $oCssFile->save(false);

        $oCssFile->setAttribute('priority', $oCssFile->id);
        $oCssFile->save(false);

        return 'Добавлено';
    }
}
