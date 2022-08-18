<?php

namespace skewer\base\site_module;

use skewer\base\site\Layer;
use yii\web\ServerErrorHttpException;

/**
 * Класс-помошник для работы с именами модулей
 * Class Module.
 */
class Module
{
    /**
     * Формирует имя класса по укороченному псевдониму.
     *
     * @param string $sAlias
     * @param string $sLayer
     * @param string $sType
     *
     * @throws ServerErrorHttpException
     *
     * @return string
     */
    public static function getClass($sAlias, $sLayer, $sType = 'Module')
    {
        if (!$sLayer) {
            $sLayer = Layer::PAGE;
        }

        $iPos = mb_strpos($sAlias, '\\');
        if ($iPos) {
            if (mb_strpos($sAlias, '\\', $iPos + 1)) {
                $sModulePathName = $sAlias;
            } else {
                list($sModuleLayer, $sModuleName) = explode('\\', $sAlias);
                $sModulePathName = 'skewer\\build\\' . $sModuleLayer . '\\' . $sModuleName . '\\' . $sType;
            }
        } else {
            $sModuleLayer = $sLayer;
            $sModuleName = $sAlias;
            $sModulePathName = 'skewer\\build\\' . $sModuleLayer . '\\' . $sModuleName . '\\' . $sType;
        }

        return $sModulePathName;
    }

    /**
     * Отдает имя класса по псевдониму или выбрасывает исключение.
     *
     * @param string $sAlias
     * @param string $sLayer
     * @param string $sType
     *
     * @throws ServerErrorHttpException
     *
     * @return string
     */
    public static function getClassOrExcept($sAlias, $sLayer, $sType = 'Module')
    {
        $sModulePathName = self::getClass($sAlias, $sLayer, $sType);

        if (!class_exists($sModulePathName)) {
            throw new ServerErrorHttpException("Class [{$sModulePathName}] not found in files.");
        }

        return $sModulePathName;
    }

    /**
     * Получить директорию модуля.
     *
     * @param string $sAlias - укороченный псевдоним модуля
     * @param string $sLayer - слой модуля
     *
     * @throws \yii\web\ServerErrorHttpException
     *
     * @return string
     */
    public static function getModuleDir($sAlias, $sLayer)
    {
        $sClassName = self::getClass($sAlias, $sLayer);
        $sModuleDir = dirname(\Yii::getAlias('@' . str_replace('\\', '/', $sClassName) . '.php')) . \DIRECTORY_SEPARATOR;

        return $sModuleDir;
    }

    /**
     * Получить директорию шаблонов модуля.
     *
     * @param string $sAlias - укороченный псевдоним модуля
     * @param string $sLayer - слой модуля
     *
     * @throws \yii\web\ServerErrorHttpException
     *
     * @return string
     */
    public static function getTemplateDir4Module($sAlias, $sLayer)
    {
        return self::getModuleDir($sAlias, $sLayer) . 'templates' . \DIRECTORY_SEPARATOR;
    }
}
