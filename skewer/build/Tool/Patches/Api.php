<?php

namespace skewer\build\Tool\Patches;

use skewer\build\Tool\Patches\models\Patch;
use skewer\components\config\ConfigUpdater;
use skewer\components\config\PatchInstaller;
use skewer\components\design\Design;
use yii\data\ActiveDataProvider;

class Api
{
    /**
     * Возвращает список доступных к установке патчей в директории сайта либо false в случае ошибки или отсутствия патчей.
     *
     * @static
     *
     * @param string $sRootPatchesDir Путь к корневой директории с патчами
     *
     * @return array|bool
     */
    public static function getAvailablePatches($sRootPatchesDir)
    {
        if (!is_dir($sRootPatchesDir)) {
            return false;
        }

        $aOut = false;

        /** @var \Directory $oDir */
        $oDir = dir($sRootPatchesDir);

        /* @noinspection PhpUndefinedFieldInspection */
        if ($oDir->handle) {
            while (false !== ($sFile = $oDir->read())) {
                if ($sFile == '.' and $sFile == '..') {
                    continue;
                }
                if (!is_dir($sRootPatchesDir . $sFile)) {
                    continue;
                }

                $sPatchFile = $sFile . \DIRECTORY_SEPARATOR . $sFile . '.php';

                if (!file_exists($sRootPatchesDir . $sPatchFile)) {
                    continue;
                }

                $aOut[] = $sPatchFile;
            }
        }// h

        $oDir->close();

        return $aOut;
    }

    // func

    /**
     * Возвращает список примененных патчей на текущей площадке.
     *
     * @static
     *
     * @param int $iPage страница постраничного
     * @param int $iOnPage Количество элементов на страницу
     *
     * @return array|bool
     */
    public static function getAppliedPatches($iPage = 0, $iOnPage = 0)
    {
        $provider = new ActiveDataProvider([
            'query' => Patch::find()
                ->orderBy(['install_date' => SORT_DESC])
                ->asArray(),
            'pagination' => [
                'page' => $iPage,
                'pageSize' => $iOnPage,
            ],
        ]);

        return  $provider->getTotalCount() ? $provider->getModels() : false;
    }

    // func

    /**
     * Возвращает количество установленных патчей.
     *
     * @return int
     */
    public static function getAppliedCount()
    {
        return (int) Patch::find()->count();
    }

    /**
     * Возвращает список доступных и установленных патчей.
     *
     * @static
     *
     * @param mixed $patchPath
     *
     * @return array
     */
    public static function getList($patchPath)
    {
        /* Запросили список доступных к установке патчей */
        $aAvailable = static::getAvailablePatches($patchPath);

        // Запросили список установленных патчей
        //      50 последних. в одном обновлении больше быть не должно
        $aApplied = static::getAppliedPatches(0, 50);

        if (!$aAvailable and !$aApplied) {
            return [];
        }

        if (!$aAvailable) {
            $aAvailable = [];
        }

        if (!$aApplied) {
            $aApplied = [];
        }

        $aAppliedUIDs = [];
        foreach ($aApplied as $iKey => $aPatch) {
            $aAppliedUIDs[$aPatch['patch_uid']] = $iKey;
        }

        $aList = [];

        foreach ($aAvailable as $sPatch) {
            $sPatchUID = basename($sPatch);

            if (empty($sPatchUID)) {
                continue;
            }

            if (isset($aAppliedUIDs[$sPatchUID])) {
                $patch = $aApplied[$aAppliedUIDs[$sPatchUID]];
                $patch['is_install'] = true;
            } else {
                $patch = [];
                $patch['patch_uid'] = $sPatchUID;
                $patch['install_date'] = \Yii::t('patches', 'no_installed');
                $patch['description'] = static::getDescFormFile($patchPath . $sPatch);
                $patch['is_install'] = false;
                $patch['file'] = $sPatch;
            }

            $aList[] = $patch;
        }

        uasort($aList, ['self', 'sortPatches']);

        return $aList;
    }

    /**
     * Функция для сортировки патчей в списке доступных.
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    public static function sortPatches($a, $b)
    {
        if ($a['is_install'] != $b['is_install']) {
            return ($a['is_install'] < $b['is_install']) ? -1 : 1;
        }

        if (isset($a['install_date']) and isset($b['install_date']) and $a['install_date'] != $b['install_date']) {
            return ($a['install_date'] < $b['install_date']) ? 1 : -1;
        }

        return ($a['file'] < $b['file']) ? -1 : 1;
    }

    /**
     * Возвращает true, если патч с UID $sPatchUID ранее устанавливался на данной площадке.
     *
     * @param string $sPatchUID
     *
     * @return bool
     */
    public static function alreadyInstalled($sPatchUID)
    {
        return (bool) Patch::findOne(['patch_uid' => $sPatchUID]);
    }

    /**
     * Отдать описание из файла.
     *
     * @static
     *
     * @param $sPath
     *
     * @return string
     */
    public static function getDescFormFile($sPath)
    {
        if (!is_file($sPath)) {
            return 'not found ' . $sPath;
        }

        // попробовать открыть и прочитать
        $sCont = file_get_contents($sPath);
        if (!$sCont) {
            return '';
        }

        // попробовать достать описание
        if (preg_match('/\$sDescription\s*=\s*[\'"]{1}(?<desc>.*)[\'"]{1};/i', $sCont, $aMatch)) {
            return $aMatch['desc'];
        }

        return $sCont;
    }

    /**
     * Установка патча.
     *
     * @param $patch_file
     * @param bool $bUseLocalDir если стоит, то будет использована локальная директория с обновлениями для пориска файлов
     * @param null|string[] $aMessages массив для вывода собщений
     *
     * @throws \skewer\components\config\UpdateException
     * @throws \yii\web\ServerErrorHttpException
     *
     * @return array|bool
     */
    public static function installPatch($patch_file, $bUseLocalDir = false, &$aMessages = null)
    {
        if ($bUseLocalDir) {
            $sPath = PATCHPATH . $patch_file;
        } else {
            $sPath = (USECLUSTERBUILD) ? CLUSTERSKEWERPATH . BUILDNUMBER . '/' . $patch_file : PATCHPATH . $patch_file;
        }

        $oInstaller = new PatchInstaller($sPath);

        ConfigUpdater::init();

        try {
            $mResult = $oInstaller->install();
        } finally {
            $aMessages = $oInstaller->getMessages();
        }

        //Установка времени последнего обновления
        Design::setLastUpdatedTime();

        ConfigUpdater::commit();

        /* Все прошло нормально - пишем о том, что патч поставили */
        $p = new Patch();

        $p->patch_uid = basename($patch_file);
        $p->install_date = date('Y-m-d h:i:s');
        $p->description = $oInstaller->getDescription();

        if (preg_match('/^\w+\/(\d+\/\d+\.php)$/', $patch_file, $match)) {
            $p->file = $match[1];
        } else {
            $p->file = $patch_file;
        }

        $p->save();

        return $mResult;
    }
}
