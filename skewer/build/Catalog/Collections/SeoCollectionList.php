<?php

namespace skewer\build\Catalog\Collections;

use skewer\components\catalog\Card;
use skewer\components\catalog\ObjectSelector;
use skewer\components\seo\SeoPrototype;

class SeoCollectionList extends SeoPrototype
{
    /**
     * Название коллекции.
     *
     * @var string
     */
    public $sCardName = '';

    /**
     * {@inheritdoc}
     */
    public static function getTitleEntity()
    {
        return 'Список коллекций';
    }

    public static function getGroup()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public static function getAlias()
    {
        return 'catalogCollection';
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

        return $aOut;
    }

    public function initSeoData()
    {
        // seo-поля пока пусты
    }

    public function loadDataEntity()
    {
        $this->aDataEntity['card'] = $this->sCardName;

        // Получение списка элементов коллекций
        $aElements = ObjectSelector::getCollections($this->sCardName)
            ->condition('active', 1)
            ->parse();

        $this->aDataEntity['items'] = $aElements;
    }

    /**
     * Задаёт имя карточки коллекции.
     *
     * @param $sCardName
     */
    public function setCard($sCardName)
    {
        $this->sCardName = $sCardName;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSearchClassName()
    {
        return ''; // Не имеет соответсующего Search - класса
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
        ];
    }
}
