<?php

namespace skewer\build\Design\CSSEditor;

use skewer\components\design\Design;

/**
 * API для работы с редактором пользовательских css файлов
 * Class Api.
 */
class Api
{
    /**
     * Проверяет наличие необходимых файлов и папок для заданного слоя.
     *
     * @throws \Exception
     */
    public static function testFileAccess()
    {
        // директория хранения
        $sDir = Design::getAddCssDirPath();

        // проверить наличие директории
        if (!is_dir($sDir)) {
            // попробовать создать
            try {
                mkdir($sDir);

                // выставлние прав. параметр mkdir иногда не срабатывает
                chmod($sDir, 0777);
            } catch (\Exception $e) {
                throw new \Exception('Директория хранения дополнительных css файлов (' . Design::cssDirPath . ') отсутствует и не может быть создана!', 0, $e);
            }
        }

        // проверка доступности на запись дополнительных файлов
        foreach (Design::getVersionList() as $sVersion) {
            $sFileName = Design::getAddCssFilePath($sVersion);
            if (file_exists($sFileName)) {
                if (!is_writable($sFileName)) {
                    throw new \Exception('Дополнительный css файл "' . Design::getLocalCssFileNameWithDir($sVersion) . '" не доступен для записи');
                }
            } else {
                if (!is_writable(Design::getAddCssDirPath())) {
                    throw new \Exception('Нет прав на запись в директорию [' . Design::cssDirPath . ']');
                }
            }
        }
    }
}
