<?php

namespace skewer\build\Adm\Params;

use skewer\base\section\Parameters;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

class TransferHelper
{
    const PARAMS_DIR_NAME = 'files/params/';

    const DATE_FORMAT_FOR_FILENAME = 'Y-m-d_H-i';

    /**
     * Формирует файл с указанными параметрами.
     *
     * @param array[] $items набор параметров
     * @param int $sectionId id экспортируемого раздела
     *
     * @throws Exception
     *
     * @return string
     */
    public static function makeFile($items, $sectionId)
    {
        if (empty($items)) {
            throw new Exception('no data');
        }
        $out = [];

        $fields = ['group', 'name', 'title', 'value', 'show_val', 'access_level'];

        foreach ($items as $row) {
            $outRow = [];
            foreach ($fields as $fieldName) {
                $outRow[$fieldName] = ArrayHelper::getValue($row, $fieldName, '');
            }
            $out[] = $outRow;
        }

        $name = self::createFileName($sectionId);
        $webFilePath = WEBPROTOCOL . WEBROOTPATH . self::PARAMS_DIR_NAME . $name;
        $fileName = WEBPATH . self::PARAMS_DIR_NAME . $name;

        if (!file_exists(WEBPATH . self::PARAMS_DIR_NAME)) {
            mkdir(WEBPATH . self::PARAMS_DIR_NAME, 0775);
        }

        if (!is_writable(WEBPATH . self::PARAMS_DIR_NAME)) {
            throw new \Exception(\Yii::t('params', 'can_not_write_file') . " [{$fileName}]");
        }
        $handle = fopen($fileName, 'w+');

        $text = json_encode(['items' => $out], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        fwrite($handle, $text);

        fclose($handle);

        return $webFilePath;
    }

    /**
     * Применяет файл для указанного раздела.
     *
     * @param string $fileName имя файла
     * @param int $sectionId id целевого раздела
     *
     * @throws \Exception
     *
     * @return int
     */
    public static function applyFile($fileName, $sectionId, &$errorMessage)
    {
        try {
            $text = file_get_contents($fileName);
        } catch (\Throwable $error) {
            $errorMessage = \Yii::t('params', 'file_not_found');
            return false;
        }


        $data = json_decode($text, true);

        if (!is_array($data) or !isset($data['items'])) {
            $errorMessage = \Yii::t('params', 'invalid_data_format');
            return false;
        }

        $cnt = 0;

        foreach ($data['items'] as $row) {
            $id = Parameters::setParams(
                $sectionId,
                ArrayHelper::getValue($row, 'group', ''),
                ArrayHelper::getValue($row, 'name', ''),
                ArrayHelper::getValue($row, 'value', ''),
                ArrayHelper::getValue($row, 'show_val', ''),
                ArrayHelper::getValue($row, 'title', ''),
                ArrayHelper::getValue($row, 'access_level', 0)
            );

            if ($id) {
                ++$cnt;
            }
        }

        return $cnt;
    }

    /**
     * Создает имя файла
     *
     * @param int $sectionId
     * @return string
     */
    private static function createFileName($sectionId)
    {
        $date = date(self::DATE_FORMAT_FOR_FILENAME);

        return "upd_params_{$sectionId}_{$date}.json";
    }
}
