<?php

namespace skewer\components\design;

use skewer\base\log\Logger;
use yii\base\UserException;
use yii\web\ServerErrorHttpException;

class Template
{
    /**
     * Заменяет наблон шапки сайта на указанный.
     *
     * @param string $sType тип переключателя
     * @param string $sName
     * @param bool $sUpdContent флаг установки стандартного контента
     */
    public static function change($sType, $sName, $sUpdContent)
    {
        $oSwitcher = self::getSwitcher($sType, $sName);

        // Получить используемый шаблон
        $sCurrentTpl = $oSwitcher->getOldTpl();

        self::resetCurrent($sType, $sCurrentTpl);

        $oSwitcher->analyzeCssParams();

        /* перекрыть шаблон для страниц */
        $oSwitcher->setTpl();

        // заменить набор модулей
        $oSwitcher->setModules();

        // заменить шаблоны для модулей
        $oSwitcher->setModuleSettings();

        // задать свое расположение для блоков
        $oSwitcher->setBlocks();

        // выполнит набор действий по установки стандартного контента
        if ($sUpdContent) {
            $oSwitcher->setContent();
        }

        // сохранить данные для восстановления
        $oSwitcher->saveBackup();

        \Yii::$app->router->updateModificationDateSite();
        \Yii::$app->rebuildCss();
    }

    /**
     * Отдает набор доступных шаблонов для шапки
     * В формате "имя" => "название".
     *
     * @param mixed $sType
     *
     * @throws UserException
     *
     * @return array
     */
    public static function getTplList($sType)
    {
        $aOut = [];

        $aDirs = scandir(RELEASEPATH . 'build/Page/Main/templates/' . $sType);
        foreach ($aDirs as $sDir) {
            if (!$sDir or $sDir[0] === '.') {
                continue;
            }

            $sClassName = '\skewer\build\Page\Main\templates\\' . $sType . '\\' . $sDir . '\\Switcher';

            if (class_exists($sClassName)) {
                /** @var TplSwitchPrototype $oSwitcher */
                $oSwitcher = new $sClassName();
                $sParentClass = self::getParentClass($sType);
                if (!is_subclass_of($oSwitcher, $sParentClass)) {
                    throw new UserException("[{$sClassName}] is not an instance of [{$sParentClass}]");
                }
                if ($oSwitcher->bUse) {
                    $aOut[$sDir] = $oSwitcher->getTitle();
                }
            }
        }

        return $aOut;
    }

    /**
     * Откатывает специфичесткие настройки текущего шаблона
     * Применяется при переключении на другой шаблон.
     *
     * @param string $sType
     * @param string $sCurrentTpl - текущий шаблон
     */
    private static function resetCurrent($sType, $sCurrentTpl)
    {
        // объект переключателя
        try {
            $oSwitcher = self::getSwitcher($sType, $sCurrentTpl);
        } catch (ServerErrorHttpException $e) {
            // это надо в случае если мы переключаемся из шаблона, который был удален на новый
            Logger::dumpException($e);
            $oSwitcher = null;
        }

        $oBackup = self::getBackupObject($sType, $sCurrentTpl);

        if ($oSwitcher) {
            $oSwitcher->resetSettingsBeforeStandard($oBackup);
        }

        if ($oBackup) {
            $oBackup->revertData();
            self::removeBackupFile($sType, $sCurrentTpl);
        }

        if ($oSwitcher) {
            $oSwitcher->resetSettingsAfterStandard($oBackup);
        }
    }

    /**
     * Отдает объект переключателя интерфейсов.
     *
     * @param string $sType тип переключателя
     * @param string $sName
     *
     * @throws ServerErrorHttpException
     * @throws UserException
     *
     * @return TplSwitchPrototype
     */
    private static function getSwitcher($sType, $sName)
    {
        $sClassName = '\\skewer\\build\\Page\\Main\\templates\\' . $sType . "\\{$sName}\\Switcher";
        if (!class_exists($sClassName)) {
            throw new ServerErrorHttpException("Class [{$sClassName}] not found");
        }
        /** @var TplSwitchPrototype $oSwitcher */
        $oSwitcher = new $sClassName();
        $sParentClass = self::getParentClass($sType);
        if (!is_subclass_of($oSwitcher, $sParentClass)) {
            throw new UserException("[{$sClassName}] is not an instance of [{$sParentClass}]");
        }

        return $oSwitcher;
    }

    /**
     * Записывает в файл данные отката шаблона.
     *
     * @param $sType
     * @param string $sTplName имя шаблона
     * @param BackupParamsPrototype $oBackup
     *
     * @throws ServerErrorHttpException
     */
    public static function writeBackupFile($sType, $sTplName, BackupParamsPrototype $oBackup)
    {
        $sFileName = PRIVATE_FILEPATH . $sType . '__' . $sTplName . '_backup.json';

        $rHandle = fopen($sFileName, 'w');

        if (fwrite($rHandle, $oBackup->getDataForSaving()) === false) {
            throw new ServerErrorHttpException('Cannot write backup file private/' . $sTplName . '_backup.json');
        }
        fclose($rHandle);
    }

    /**
     * Отдает объект с данными отката по имени шаблона.
     *
     * @param string $sType
     * @param string $sTplName
     *
     * @return null|BackupParamsPrototype
     */
    private static function getBackupObject($sType, $sTplName)
    {
        $sFileName = PRIVATE_FILEPATH . $sType . '__' . $sTplName . '_backup.json';

        if (!file_exists($sFileName)) {
            return;
        }

        $rHandle = fopen($sFileName, 'r');

        $sContent = fread($rHandle, filesize($sFileName));
        fclose($rHandle);

        $oSwitcher = self::getSwitcher($sType, $sTplName);
        $sBackupClass = $oSwitcher::getBackupClass();

        return new $sBackupClass(json_decode($sContent, true));
    }

    /**
     * Удаляет файл  восстановления дл шаблона.
     *
     * @param $sType
     * @param $sTplName
     */
    private static function removeBackupFile($sType, $sTplName)
    {
        $sFileName = PRIVATE_FILEPATH . $sType . '__' . $sTplName . '_backup.json';
        if (file_exists($sFileName)) {
            unlink($sFileName);
        }
    }

    /**
     * Отдает имя родительского класс переключателя в зависимости от типа.
     *
     * @param $sType
     *
     * @return string
     */
    private static function getParentClass($sType)
    {
        return '\\' . __NAMESPACE__ . '\TplSwitch' . ucfirst($sType);
    }
}
