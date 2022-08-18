<?php

namespace skewer\build\Tool\SeoGen\exporter;

class Api
{
    /** @var string Событие сбора списка классов */
    const EVENT_GET_LIST_EXPORTERS = 'get_list_exporters';

    /** @var array Кеш списка классов exporter */
    private static $aListExporters = [];

    /**
     * Получить экземпляр класса exporter по псевдониму.
     *
     * @param string $sAlias
     *
     * @return Prototype | null
     */
    public static function getExporterByAlias($sAlias)
    {
        $aListImporters = self::getListExporters();

        if (isset($aListImporters[$sAlias])) {
            $oImporter = new $aListImporters[$sAlias]();

            return $oImporter;
        }
    }

    /**
     * Получить список классов exporter.
     *
     * @return array
     */
    public static function getListExporters()
    {
        if (!self::$aListExporters) {
            $oEvent = new GetListExportersEvent();
            \Yii::$app->trigger(self::EVENT_GET_LIST_EXPORTERS, $oEvent);
            self::$aListExporters = $oEvent->getList();
        }

        return self::$aListExporters;
    }

    /**
     * Получить список названий выгружаемых сущностей.
     *
     * @return array
     */
    public static function getListTitleExporters()
    {
        $aListImporters = self::getListExporters();

        $aOut = [];

        foreach ($aListImporters as $sName => $sNamespace) {
            /** @var Prototype $oExporter */
            $oExporter = new $sNamespace();
            $aOut[$sName] = $oExporter->getTitle();
        }

        asort($aOut);

        return $aOut;
    }
}
