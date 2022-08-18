<?php

namespace skewer\build\Catalog\Collections;

use skewer\base\section\Parameters;
use skewer\build\Page\CatalogViewer;
use skewer\build\Page\Main;
use skewer\components\catalog\Card;
use skewer\components\catalog\ObjectSelector;
use skewer\components\seo;
use skewer\components\seo\SeoPrototype;
use yii\helpers\ArrayHelper;

class SeoElementCollection extends SeoPrototype
{
    /**
     * Имя карточки элемента коллекции.
     *
     * @var string
     */
    public $sCardName = '';

    /**
     * {@inheritdoc}
     */
    public static function getTitleEntity()
    {
        return 'Элемент коллекции';
    }

    public static function getGroup()
    {
        return 'collection';
    }

    /**
     * {@inheritdoc}
     */
    public static function getAlias()
    {
        return 'catalogElementCollection';
    }

    public function __construct($iEntityId = 0, $iSectionId = 0, $aDataEntity = [], $sCardName = '')
    {
        $this->sCardName = $sCardName;
        parent::__construct($iEntityId, $iSectionId, $aDataEntity);
    }

    /**
     * {@inheritdoc}
     */
    public function extractReplaceLabels($aParams)
    {
        $aOut = [];

        $sCardTitle = Card::getTitle($this->sCardName);
        $aOut['label_collection_title_upper'] = $sCardTitle;
        $aOut['label_collection_title_lower'] = $this->toLower($sCardTitle);

        $aOut['label_collection_element_title_upper'] = ArrayHelper::getValue($this->aDataEntity, 'title', '');
        $aOut['label_collection_element_title_lower'] = $this->toLower(ArrayHelper::getValue($this->aDataEntity, 'title', ''));

        if (isset($aParams['label_number_photo'])) {
            $aOut['label_number_photo'] = $aParams['label_number_photo'];
        }

        return $aOut;
    }

    public function initSeoData()
    {
        $iEntityId = $this->iEntityId ? $this->iEntityId : ArrayHelper::getValue($this->aDataEntity, 'id', 0);

        if ($aSeoData = seo\Api::get(static::getGroup(), $iEntityId, $this->iSectionId, true)) {
            \Yii::configure($this, $aSeoData);
        }
    }

    /**
     * Задаёт имя карточки элемента коллекции.
     *
     * @param $sCardName
     */
    public function setCard($sCardName)
    {
        $this->sCardName = $sCardName;
    }

    public function loadDataEntity()
    {
        $aCollectionData = [];
        if ($this->iEntityId && $this->sCardName) {
            $aCollectionData = ObjectSelector::get($this->iEntityId, $this->sCardName);
        }

        $this->aDataEntity = ($aCollectionData) ? $aCollectionData : [];
    }

    public function getPriority()
    {
        // базовое значение для коллекций берём по шаблону каталога
        $iTpl = self::getIdTemplateCatalog();
        $aSeoData = seo\Api::get(Main\Seo::getGroup(), $iTpl, $iTpl, true);

        return ArrayHelper::getValue($aSeoData, 'priority', 0);
    }

    public function calculateFrequency()
    {
        // базовое значение для коллекций берём по шаблону каталога
        $iTpl = self::getIdTemplateCatalog();
        $aSeoData = seo\Api::get(Main\Seo::getGroup(), $iTpl, $iTpl, true);

        return ArrayHelper::getValue($aSeoData, 'frequency', '');
    }

    /**
     * Получить id шаблона каталога.
     *
     * @return bool|int
     */
    private static function getIdTemplateCatalog()
    {
        /** @var array Массив шаблонов $aTemplates */
        $aTemplates = \skewer\base\section\Tree::getSubSections(\Yii::$app->sections->templates(), true, true);

        $aParams = Parameters::getList()
            ->parent($aTemplates)
            ->group('content')
            ->name(Parameters::object)
            ->value(CatalogViewer\Module::getNameModule())
            ->asArray()->get();

        return (isset($aParams[0]['parent']))
                ? (int) $aParams[0]['parent']
                : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchObject()
    {
        /** @var Search $oSearch */
        $oSearch = parent::getSearchObject();
        $oSearch->setCard($this->sCardName);

        return $oSearch;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSearchClassName()
    {
        return Search::className();
    }

    /**
     * {@inheritdoc}
     */
    public function editableSeoTemplateFields()
    {
        return [
            'title',
            'description',
            'keywords',
            'altTitle',
        ];
    }
}
