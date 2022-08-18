<?php

namespace skewer\components\search;

use yii\base\Event;
use yii\helpers\ArrayHelper;

/**
 * Класс событие для сбора данных поиска по CMS.
 *
 * @property int $limit ограничение по количиеству строк от одного класса
 */
class CmsSearchEvent extends Event
{
    /** @var string поисковый запрос */
    public $query = '';

    /** @var CmsSearchRow[] */
    private $aData = [];

    /**
     * @param array|CmsSearchRow $mRow
     */
    public function addRow($mRow)
    {
        if (is_array($mRow)) {
            $this->aData[] = new CmsSearchRow($mRow);
        } elseif ($mRow instanceof  CmsSearchRow) {
            $this->aData[] = $mRow;
        } else {
            throw new \InvalidArgumentException('mRow must be an array or instance of CmsSearchRow');
        }
    }

    /**
     * Отдает данные поиска.
     *
     * @return CmsSearchRow[]
     */
    public function getData()
    {
        return $this->aData;
    }

    /**
     * Отдает ограничение по количиеству строк от одного класса.
     * По умолчанию 5.
     *
     * Можно модифицировать без отцепления, прописав в /config/web.php
     * ```
     * $localConfig['params']['cms_search_limit'] = 30;
     * ```
     *
     * @return int
     */
    public function getLimit()
    {
        return ArrayHelper::getValue(\Yii::$app->params, 'cms_search_limit', 5);
    }
}
