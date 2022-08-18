<?php

namespace skewer\build\Cms\FileBrowser;

use skewer\base\section;
use skewer\base\site_module\Context;
use skewer\build\Cms;
use skewer\components\auth\CurrentAdmin;
use skewer\components\auth\Policy;

/** Внешний интерфейс для работы с библиотеками хранения загружаемых файлов */
class Api
{
    /** Псевдоним библиотеки файлов по умолчанию */
    const DEF_LIB_ALIAS = 'lib_images';

    /**
     * @param $sModuleClassName
     *
     * @return string
     */
    public static function getAliasByModule($sModuleClassName)
    {
        $aPathItems = explode('\\', trim($sModuleClassName, '\\'));
        array_pop($aPathItems);
        $sModuleName = array_pop($aPathItems);
        $sLayerName = array_pop($aPathItems);

        return  $sLayerName . '_' . $sModuleName;
    }

    /**
     * Получить id раздела библиотеки файлов по псевдониму раздела
     * * ВНИМАНИЕ! Псевдоним можно задать в виде: [слой]_[модуль] (пример: Adm_Catalog) тогда метод
     * запросит имя модуля и попытается создать новую библиотеку файлов для этого модуля, если её ещё не существует
     *
     * @param string $sFolderAlias псевдоним раздела
     *
     * @throws \Exception
     *
     * @return \Exception|int
     */
    public static function getSectionIdbyAlias($sFolderAlias)
    {
        // проверяем имя модуля
        if (!$sFolderAlias) {
            throw new \Exception('Имя модуля не задано');
        }
        // id раздела библиотек
        $iLibSectionId = \Yii::$app->sections->library();

        // проверяем наличие раздела
        $iSectionId = section\Tree::getSectionByAlias($sFolderAlias, $iLibSectionId);

        // есть - отдать id
        if ($iSectionId) {
            return $iSectionId;
        }

        if (mb_strpos($sFolderAlias, '_')) {
            list($sLayer, $sModule) = explode('_', $sFolderAlias, 2);
            $sClassName = sprintf('\skewer\build\%s\%s\Module', $sLayer, $sModule);

            if (!class_exists($sClassName)) {
                $oConfig = \Yii::$app->register->getModuleConfig($sModule, $sLayer);
                if ($oConfig) {
                    $sClassName = $oConfig->getNameWithNamespace();
                }
            }
        } else {
            $sClassName = $sFolderAlias;
        }

        if (!class_exists($sClassName)) {
            throw new \Exception("Раздел [{$sFolderAlias}] не найден");
        }
        /** @var Cms\Tabs\ModulePrototype $oModule объект модуля */
        $oModule = new $sClassName(new Context('sub', $sClassName, ctModule, []));

        // проверяем классовую принадлежность
        if (!($oModule instanceof Cms\Tabs\ModulePrototype)) {
            throw new \Exception("Модуль [{$sClassName}] должен быть унаследован от интерфейса AdminTabModulePrototype");
        }
        // достаем имя модуля
        $sModuleTitle = $oModule->getTitle();

        $section = section\Tree::addSection($iLibSectionId, $sModuleTitle, 0, $sFolderAlias, section\Visible::VISIBLE);
        $section->type = section\Tree::typeDirectory;
        $section->save();
        $iSectionId = $section->id;

        // есть - отдать id
        if ($iSectionId) {
            /* Обновление кеша политик доступа */
            Policy::incPolicyVersion();
            CurrentAdmin::reloadPolicy();

            return $iSectionId;
        }

        throw new \Exception("Ошибка создания новой папки для [{$sFolderAlias}]");
    }
}
