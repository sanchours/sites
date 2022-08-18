<?php

namespace skewer\build\Page\CatalogMaps;

use skewer\base\section\Tree;
use skewer\base\site_module;
use skewer\base\SysVar;
use yii\helpers\StringHelper;

class Module extends site_module\page\ModulePrototype implements site_module\ExcludedParametersInterface
{
    /** @const Имя группы параметров модуля */
    const group_params_module = 'CatalogMaps';

    /** @var string Список каталожных разделов, объекты которых выводятся на карту */
    public $sSourceSections;

    /** @var bool Выбирать маркеры товаров-модификаций ? */
    public $bShowModification = false;

    /** @var int id карты */
    public $iMapId;

    /** @var string Заголовок блока в разделе */
    public $sTitleBlock = '';

    /** @var string шаблон */
    public $template = 'main.php';

    public function init()
    {
        $this->setParser(parserPHP);

        return true;
    }

    public function execute()
    {
        $aSourceSections = StringHelper::explode($this->sSourceSections, ',', true, true);
        $aSourceSections = array_intersect(Tree::getVisibleSections(), $aSourceSections);

        $aMarkers = Api::getMarkersFromCatalogSections(
            $aSourceSections,
            $this->getModuleDir() . \DIRECTORY_SEPARATOR . $this->getTplDirectory() . \DIRECTORY_SEPARATOR . 'infoWindow.php',
            $this->bShowModification
        );

        $sHtmlMap = Api::buildMap($aMarkers, $this->iMapId);

        if ($sHtmlMap === false) {
            return psBreak;
        }

        $this->setData('map', $sHtmlMap);
        $this->setData('titleBlock', $this->sTitleBlock);
        $this->setTemplate($this->template);

        return psComplete;
    }

    /**
     * {@inheritdoc}
     */
    public static function getExcludedParameters()
    {
        $aOut = [];

        $bModificationsEnabled = (bool) SysVar::get('catalog.goods_modifications', false);

        if (!$bModificationsEnabled) {
            $aOut[Api::paramNameShowModification] = Api::paramNameShowModification;
        }

        return $aOut;
    }
}
