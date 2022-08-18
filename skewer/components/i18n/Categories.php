<?php

namespace skewer\components\i18n;

use skewer\components\config\installer;
use skewer\components\i18n\models\LanguageValues;
use yii\helpers\ArrayHelper;

/**
 * Класс для работы с категориями сообщений
 * Class Modules.
 */
class Categories
{
    /**
     * набор дополнительных языковых категорий.
     *
     * @var array
     */
    public static $aAddLangList = [
        'ft' => 'base/ft/Language.php',
        'catalog' => 'components/catalog/Language.php',
        'app' => 'base/site/Language.php',
        'gallery' => 'components/gallery/Language.php',
        'rating' => 'components/rating/Language.php',
        'content_generator' => 'components/content_generator/Language.php',
    ];

    /**
     * набор дополнительных файлов с данными.
     *
     * @var array
     */
    public static $aAddPresetDataList = [
        'app' => 'base/site/PresetData.php',
        'catalog' => 'components/catalog/PresetData.php',
        'gallery' => 'components/gallery/PresetData.php',
    ];

    /**
     * Регистрирует новые языковые значения модуля. Старые при этом не стираются.
     *
     * @param installer\Module $module
     */
    public static function updateModuleLanguageValues(installer\Module $module)
    {
        self::updateByCategory($module->languageCategory, $module->languageFile);
        self::updateByCategory($module->languageCategory, $module->presetDataFile, true);
    }

    /**
     * Возвращает содержимое словаря, который расположен в $LangFilePath в виде массива.
     *
     * @param $sLangFilePath
     *
     * @return array
     */
    private static function getModuleLanguageValues($sLangFilePath)
    {
        return (file_exists($sLangFilePath) && !is_dir($sLangFilePath)) ? require_once($sLangFilePath) : [];
    }

    /**
     * Обновляет в базе данные категории из файла. Старые значения не удаляются.
     *
     * @param $sCategory
     * @param $sFileName
     * @param bool $bData - флаг того, что данные являются предустановленным контентом
     */
    public static function updateByCategory($sCategory, $sFileName, $bData = false)
    {
        $aMessages = self::getModuleLanguageValues($sFileName);

        /*
         * При обновлении данных для категории нет удаления, так как мы не знаем всех источников
         * Для очистки нужно использовать полное перестроение
         */

        if (!$aMessages || !is_array($aMessages)) {
            return;
        }

        foreach ($aMessages as $sLang => $aValues) {
            Messages::setValues(
                $aValues,
                $sLang,
                $sCategory,
                $bData
            );
        }

        \Yii::$app->getI18n()->clearCacheByCategory($sCategory);
    }

    /**
     * Список категорий.
     *
     * @return array
     */
    public static function getCategoryList()
    {
        return ArrayHelper::map(LanguageValues::find()->groupBy(['category'])->select(['category'])->orderBy(['category' => SORT_ASC])->asArray()->all(), 'category', 'category');
    }
}
