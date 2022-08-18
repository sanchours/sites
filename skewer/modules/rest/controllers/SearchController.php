<?php

namespace skewer\modules\rest\controllers;

use skewer\base\SysVar;
use skewer\components\search\Api;

/**
 * Каталожный поиск
 * Class SearchController.
 */
class SearchController extends PrototypeController
{
    /** @var string Набор полей для выдачи списка товара (без пробелов) */
    private static $sListFields = 'id,title,article,price,currency,announce,gallery';

    /** @var int Общий поиск */
    const ALL_TYPE_SEARCH = 0;
    /** @var int Информационный поиск */
    const INFO_TYPE_SEARCH = 1;
    /** @var int Каталожный поиск */
    const CATALOG_TYPE_SEARCH = 2;

    /**
     * Максимальна длина текста, оображаемого в результатах поиска.
     *
     * @var int
     */
    private $iLength = 500;

    /** @var int Тип поиска */
    private $type = 1;

    /** @var int секция для поиска */
    private $searchSection = '';
    /** @var bool вывод подсекций */
    private $bSubsection = true;

    const MAX_VALUE = 500;
    const MIN_VALUE = 20;

    public function actionIndex()
    {
        $iPage = (int) \Yii::$app->request->get('page', 1);
        $sSearchText = \Yii::$app->request->get('search_text');
        $iOnPage = (int) \Yii::$app->request->get('onpage', self::MIN_VALUE);
        $search_type = (int) \Yii::$app->request->get('search_type', self::ALL_TYPE_SEARCH);

        if ($iOnPage > self::MAX_VALUE) {
            $iOnPage = self::MAX_VALUE;
        } elseif ($iOnPage <= 0) {
            $iOnPage = self::MIN_VALUE;
        }

        if ($search_type < 0 || $search_type > 2) {
            $search_type = (int) SysVar::get('Search.default_type');
        }

        if (!empty($sSearchText)) {
            switch ($search_type) {
                case self::ALL_TYPE_SEARCH:
                case self::INFO_TYPE_SEARCH:
                default:
                    $aResult = Api::getInfoData($sSearchText, $iOnPage, $iPage, $this->type, $search_type, $this->searchSection, $this->bSubsection, $this->iLength);
                    $aResult = ($aResult) ? $aResult['items'] : $aResult;
                    $aResult = $this->parseFieldSearch($aResult);
                    break;

                case self::CATALOG_TYPE_SEARCH:
                    $aResult = Api::getCatalogData($sSearchText, $iOnPage, $iPage, $this->type, $search_type, $this->searchSection, $this->bSubsection, 1);
                    $aResult = $this->parseFieldCatalog($aResult);
                    break;
            }

            // ! Постраничник должен устанавливаться для списков даже при пустой выборке
            $iTotalCount = count($aResult);
            $this->setPagination($iTotalCount, ceil($iTotalCount / $iOnPage), $iPage, $iOnPage);

            return $aResult;
        }

        return [];
    }

    //func

    /**
     * Обработка полей каталожного поиска.
     *
     * @param $aResult
     *
     * @return array
     */
    private function parseFieldCatalog($aResult)
    {
        if (!$aResult) {
            return [];
        }

        $aFields = array_flip(explode(',', self::$sListFields));

        $aGoodsOut = [];

        foreach ($aResult  as &$paData) {
            $aGoodsOut[] = CatalogController::parseFields($aFields, $paData, true);
        }

        return $aGoodsOut;
    }

    /**
     * Обработка полей общего и информационного поиска.
     *
     * @param array $aResult
     *
     * @return array
     */
    private function parseFieldSearch($aResult)
    {
        if (!$aResult) {
            return [];
        }
        $aNotShowField = ['search_text'];
        foreach ($aResult  as &$paData) {
            foreach ($paData as $sNameField => $sValue) {
                if (in_array($sNameField, $aNotShowField)) {
                    unset($paData[$sNameField]);
                }
            }
        }

        return $aResult;
    }
}//class
