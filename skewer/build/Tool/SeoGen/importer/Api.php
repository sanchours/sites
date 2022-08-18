<?php

namespace skewer\build\Tool\SeoGen\importer;

use skewer\base\section\Parameters;
use skewer\base\section\Template;
use skewer\base\section\Tree;
use skewer\build\Adm\Tree\Search;
use skewer\build\Page\Main;
use skewer\components\seo;
use skewer\helpers\ImageResize;
use yii\helpers\ArrayHelper;

class Api
{
    /** @var string Событие сбора списка классов */
    const EVENT_GET_LIST_IMPORTERS = 'get_list_importers';

    /** @var array Кеш списка классов importer */
    private static $aListImporters = [];

    /**
     * Метод проверки существования записи.
     * Вернёт id записи или false если запись не найдена.
     *
     * @param $sEntityType - тип сущности
     * @param $sAliasRecord - alias записи
     *
     * @return bool|int
     */
    public static function doExistRecord($sEntityType, $sAliasRecord)
    {
        /** @var seo\SeoPrototype $oSeo */
        if (!class_exists($sEntityType) || !(($oSeo = new $sEntityType()) instanceof seo\SeoPrototype)) {
            return false;
        }

        return $oSeo->doExistRecord($sAliasRecord);
    }

    /**
     * Возвращает обработанный относительный url.
     *
     * @param string $sUrl - урл
     *
     * @return mixed
     */
    public static function getRequestUriFromAbsoluteUrl($sUrl)
    {
        $sUrl = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $sUrl);
        $sUrl = trim($sUrl, '/');

        return (!$sUrl) ? '/' : '/' . $sUrl . '/';
    }

    /**
     * Метод обновления данных сущности.
     *
     * @param $iEntityType - тип сущности
     * @param $iEntityId - id сущности
     * @param $iSectionId - id раздела
     * @param $aData - данные
     *
     * @return bool        - true - успешное обновление, false - запись не обновлена
     */
    public static function updateEntity($iEntityType, $iEntityId, $iSectionId, $aData)
    {
        switch ($iEntityType) {
            case Main\Seo::className():

                if (isset($aData['h1'])) {
                    if ($oH1Param = Parameters::getByName($iSectionId, 'title', 'altTitle')) {
                        $oH1Param->value = $aData['h1'];
                        $oH1Param->save();
                    }
                }

                if (isset($aData['staticContent'])) {
                    if ($oStContentParam = Parameters::getByName($iSectionId, 'staticContent', 'source')) {
                        $sText = html_entity_decode($aData['staticContent']);
                        $oStContentParam->show_val = ImageResize::wrapTags($sText, $iSectionId);
                        $oStContentParam->save();
                    }
                }

                if (isset($aData['staticContent2'])) {
                    if ($oStContentParam = Parameters::getByName($iSectionId, 'staticContent2', 'source')) {
                        $sText = html_entity_decode($aData['staticContent2']);
                        $oStContentParam->show_val = ImageResize::wrapTags($sText, $iSectionId);
                        $oStContentParam->save();
                    }
                }

                $oSearchComponent = new Search();
                $oSearchComponent->updateByObjectId($iSectionId, false);

                break;
        }

        if (!class_exists($iEntityType)) {
            return false;
        }

        /** @var seo\SeoPrototype $oSeo */
        $oSeo = new $iEntityType();
        if (!($oSeo instanceof seo\SeoPrototype)) {
            return false;
        }

        $oSeo->setEntityId($iEntityId);
        $oSeo->setSectionId($iSectionId);
        $oSeo->loadDataEntity();
        $oSeo->initSeoData();

        $aSeoData = [];
        foreach (seo\SeoPrototype::getField4Parsing() as $item) {
            if (isset($aData[$item]) && (seo\Api::prepareRawString($oSeo->parseField($item, ['sectionId' => $iSectionId])) !== seo\Api::prepareRawString($aData[$item]))) {
                $aSeoData[$item] = seo\Api::prepareRawString($aData[$item]);
            }
        }

        seo\Api::set($oSeo::getGroup(), $iEntityId, $iSectionId, $aSeoData);

        return true;
    }

    /**
     * Получить экземпляр класса importer по псевдониму.
     *
     * @param string $sAlias
     *
     * @return Prototype | null
     */
    public static function getImporterByAlias($sAlias)
    {
        $aListImporters = self::getListImporters();

        if (isset($aListImporters[$sAlias])) {
            $oImporter = new $aListImporters[$sAlias]();

            return $oImporter;
        }
    }

    /**
     * Получить список классов importer.
     *
     * @return array
     */
    public static function getListImporters()
    {
        if (!self::$aListImporters) {
            $oEvent = new GetListImportersEvent();
            \Yii::$app->trigger(self::EVENT_GET_LIST_IMPORTERS, $oEvent);
            //список импортёров
            self::$aListImporters = $oEvent->getList();
        }

        return self::$aListImporters;
    }

    /**
     * Получить список названий выгружаемых сущностей.
     *
     * @return array
     */
    public static function getListTitleImporters()
    {
        $aListImporters = self::getListImporters();

        $aOut = [];

        foreach ($aListImporters as $sName => $sNamespace) {
            /** @var Prototype $oImporter */
            $oImporter = new $sNamespace();
            $aOut[$sName] = $oImporter->getTitle();
        }

        asort($aOut);

        return $aOut;
    }

    /**
     * Получить "разделы для импорта", разделы, в которые будут импортироваться данные.
     *
     * @param string $sModuleName -  имя модуля, для получения шаблона
     *
     * @return array
     */
    public static function getSections4ImportByModuleName($sModuleName)
    {
        $iTemplateId = Template::getTemplateIdForModule($sModuleName);

        if (!$iTemplateId) {
            return [];
        }

        $aIdSectionsByTemplate = Template::getSubSectionsByTemplate($iTemplateId);

        if (!$aIdSectionsByTemplate) {
            return [];
        }

        $aSectionsByTemplate = Tree::getSections($aIdSectionsByTemplate, true, true);

        $aIdToTitleMap = ArrayHelper::map($aSectionsByTemplate, 'id', static function ($item) {
            return sprintf('%s (%d)', $item['title'], $item['id']);
        });

        $aSections = ['all' => 'Все'] + $aIdToTitleMap;

        return $aSections;
    }
}
