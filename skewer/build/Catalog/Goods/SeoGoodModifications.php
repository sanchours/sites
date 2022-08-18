<?php

namespace skewer\build\Catalog\Goods;

use skewer\components\seo\Template;

class SeoGoodModifications extends SeoGood
{
    /**
     * id базового товара.
     *
     * @var int
     */
    protected $iBaseGoodId;

    /**
     * {@inheritdoc}
     */
    public static function getTitleEntity()
    {
        return 'Товар-модификация';
    }

    /**
     * {@inheritdoc}
     */
    public static function getAlias()
    {
        return 'catalogDetail2Layer';
    }

    /**
     * Установить id базового товара.
     *
     * @param $iBaseGoodId
     */
    public function setBaseGoodId($iBaseGoodId)
    {
        $this->iBaseGoodId = $iBaseGoodId;
    }

    /**
     * Получить id базового товара.
     *
     * @return int | null
     */
    public function getBaseGoodId()
    {
        return $this->iBaseGoodId;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndividualTemplate4Section()
    {
        return Template::getByAliases(parent::getAlias(), $this->iSectionId);
    }
}
