<?php

namespace skewer\app;

use skewer\base\Twig;
use skewer\components\config\installer;
use skewer\components\design\DesignManager;
use yii\helpers\FileHelper;

/**
 * Набор методов для перестрения кэша и обновления сайтовых данных.
 */
trait CacheDropTrait
{
    /*
     * Блок методов очистки кэша
     * Набор проксирующих методов для быстрого доступа
     */

    /**
     * Очищает директорию assets cо скомпилированными файлами клиентской части.
     */
    public function clearAssets()
    {
        FileHelper::removeDirectory(WEBPATH . 'assets/');
        FileHelper::createDirectory(WEBPATH . 'assets/');
    }

    /**
     * Очищает директорию языкововй файловый кэш.
     */
    public function clearLang()
    {
        \Yii::$app->getI18n()->clearCache();
    }

    /**
     * Очищает файловый кэш Twig.
     */
    public function clearParser()
    {
        Twig::clearCache();
    }

    /*
     * Блок методов методов перестроения внутренних данных и структур
     * Набор проксирующих методов для быстрого доступа
     */

    /**
     * Перестраивает базу css параметров, дополняя её новыми значениями.
     */
    public function rebuildCss()
    {
        DesignManager::analyzeAllCssFiles();
    }

    /**
     * Перестраивает реестр установленных модулей
     * Обновляются данные из конфигов этих модулей.
     */
    public function rebuildRegistry()
    {
        \Yii::$app->register->actualizeAllModuleConfig();
    }

    /**
     * Перестраиваются словари установленных модулей
     * Все метки, не измененные вручную, будут обновлены на то, что лежит в файлах
     * Применяется только для системных языков (en, ru)
     * Если стоит флаг очистки, то все текущеи неперекрытые значения для базовых
     * языков будут сначала стерты, а потом восстановлены.
     *
     * @param bool $sResetBase флаг очистки базы
     */
    public function rebuildLang($sResetBase = true)
    {
        $installer = new installer\Api();
        $installer->updateAllModulesLang($sResetBase);
    }
}
