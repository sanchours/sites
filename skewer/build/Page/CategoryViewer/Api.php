<?php

namespace skewer\build\Page\CategoryViewer;

use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\base\site\Layer;
use skewer\base\site_module;
use skewer\build\Adm\CategoryViewer\models\CategoryViewerCssParams;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class Api
{
    /**
     * Получить виджет разводки по id раздела.
     *
     * @param int $iSectionId - ид раздела
     *
     * @return false|string
     */
    public static function getWidgetBySection($iSectionId)
    {
        return Parameters::getValByName($iSectionId, 'CategoryViewer', 'category_widget', true);
    }

    /**
     * Получить css параметры по имени виджета.
     *
     * @param string $sWidget
     *
     * @throws UserException
     *
     * @return array
     */
    public static function getCssParamsByWidget($sWidget)
    {
        $sCategoryViewerTplDirectory = site_module\Module::getTemplateDir4Module(Module::getNameModule(), Layer::PAGE);
        $sFilePathParameters = $sCategoryViewerTplDirectory . $sWidget . \DIRECTORY_SEPARATOR . 'parameters.php';

        $aParams = [];
        if (!file_exists($sFilePathParameters)) {
            throw new UserException("Файл с параметрами разводки [{$sFilePathParameters}] не существует");
        }
        $aParams = require $sFilePathParameters;

        return $aParams;
    }

    /**
     * Получить доступные виджеты разводки.
     *
     * @return array
     */
    public static function getAvailableWidgets()
    {
        $aOut = [];

        $aDirs = scandir(RELEASEPATH . 'build/Page/Main/templates/categoryViewer');
        foreach ($aDirs as $sDir) {
            if (!$sDir or $sDir[0] === '.') {
                continue;
            }
            $aOut[] = $sDir;
        }

        return $aOut;
    }

    /**
     * Получить css параметры разделов разводки по списку разделов и виджету
     * Дефолтные значения параметров(из файлов /parameters.php) будут перекрыты параметрами раздела.
     *
     * @param string $sWidget - виджет
     * @param array|int $mSections - ид раздела/-ов
     *
     * @return array
     */
    public static function getCssParamsBySections($sWidget, $mSections)
    {
        $aOut = [];

        $aDefCssParams = self::getCssParamsByWidget($sWidget);

        $aDefCssParams = ArrayHelper::map(
            $aDefCssParams,
            static function ($item) {
                return $item['groupName'] . ';' . $item['paramName'];
            },
            static function ($item) {
                return $item['defValue'];
            }
        );

        $aListSectionsId = is_array($mSections) ? $mSections : [$mSections];

        $aIndividualCssParams = CategoryViewerCssParams::getParamsBySections($aListSectionsId);

        $aIndividualCssPramsGroupBySection = [];

        foreach ($aIndividualCssParams as $aParam) {
            $sKey = $aParam['group'] . ';' . $aParam['paramName'];
            $aIndividualCssPramsGroupBySection[$aParam['sectionId']][$sKey] = $aParam['value'];
        }

        foreach ($aListSectionsId as $iSectionId) {
            if (isset($aIndividualCssPramsGroupBySection[$iSectionId])) {
                $aOut[$iSectionId] = array_merge($aDefCssParams, $aIndividualCssPramsGroupBySection[$iSectionId]);
            } else {
                $aOut[$iSectionId] = $aDefCssParams;
            }
        }

        return $aOut;
    }

    /**
     * Рекурсивно включает/выключает всем потомкам раздела вывод категорий.
     *
     * @param int $iSectionId
     * @param bool $bVal
     */
    public static function toggleShowCategory4SubSections($iSectionId, $bVal)
    {
        $iVal = (bool) $bVal;

        $aSubSections = Tree::getAllSubsection($iSectionId);

        $aCategoryParentParam = [
            'name' => 'category_parent',
            'title' => 'categoryViewer.category_parent',
            'value' => $iVal,
            'group' => 'CategoryViewer',
            'parent' => $iSectionId,
            'access_level' => 0,
            'show_val' => '',
        ];

        Parameters::addParam($aCategoryParentParam);

        foreach ($aSubSections as $iSectionId) {
            Parameters::addParam([
                'name' => 'category_show',
                'title' => 'categoryViewer.param_show',
                'value' => $iVal,
                'group' => 'CategoryViewer',
                'parent' => $iSectionId,
                'access_level' => 0,
                'show_val' => '',
            ]);

            Parameters::addParam(
                array_merge($aCategoryParentParam, ['parent' => $iSectionId])
            );
        }
    }
}
