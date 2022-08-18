<?php

namespace skewer\helpers;

use skewer\base\section\Tree;
use skewer\base\site\Page;
use skewer\base\site\Site;
use skewer\components\regions\RegionHelper;
use yii\helpers\ArrayHelper;

/**
 * @class Paginator
 *
 * @author ArmiT, $Author$
 *
 * @version $Revision$
 * @date $Date$
 * @project Skewer
 *
 * @example
 * $aURLParams['NewsModule'] array('onpage'=>100);
 * $aParams['onPage'] = 100;
 * $aParams['onGroup'] = 10;
 * $aParams['useGroups'] = true;
 * $aParams['useEdges'] = true;
 * $aParams['useItems'] = true;
 * Paginator::getPageLine(1, 100, 5, $aURLParams, $aParams);
 */
class Paginator
{
    /**
     * Построение пагинационных страниц.
     *
     * @param int $iPage - номер страницы
     * @param int $iCount - общее количество
     * @param int $iSectionId - номер секции
     * @param array $aURLParams
     * @param array $aParams  - $aParams['onGroup'] - кол-во страниц пагинации
     * @param bool $bHideCanonicalPagination - Флаг запрещающий в код вставлять canonical пагинации
     *
     * @return array $aPages
     */
    public static function getPageLine($iPage, $iCount, $iSectionId, $aURLParams = [], $aParams = [], $bHideCanonicalPagination = false)
    {
        if ($iPage <= 0) {
            $iPage = 1;
        }

        // init default settings
        $iOnPage = (isset($aParams['onPage'])) ? $aParams['onPage'] : 10;
        $iOnGroup = (isset($aParams['onGroup'])) ? $aParams['onGroup'] : 10;
        $bUseEdges = (isset($aParams['useEdges'])) ? $aParams['useEdges'] : true;
        $bUseItems = (isset($aParams['useItems'])) ? $aParams['useItems'] : true;
        $bUsePages = (isset($aParams['usePages'])) ? $aParams['usePages'] : true;
        $bUseGroups = (isset($aParams['useGroups'])) ? $aParams['useGroups'] : true;
        //$bUseShortKeys = (isset($aParams['useShortKeys']))? $aParams['useShortKeys']: true;

        $aPages['goodId'] = (isset($aParams['goodId'])) ? $aParams['goodId'] : '';

        if (!$iOnPage) {
            return false;
        }
        if (!count($aURLParams)) {
            return false;
        }

        reset($aURLParams);
        $aGet = [];
        list($sModuleName, $aURLParams) = each($aURLParams);

        if (count($aURLParams)) {
            foreach ($aURLParams as $sKey => $sValue) {
                $aGet[] = $sKey . '=' . urlencode($sValue);
            }
        }

        $aPages['page'] = $iPage;
        $aPages['module'] = $sModuleName;
        $aPages['sectionId'] = $iSectionId;
        $aPages['parameters'] = (count($aGet)) ? implode('&', $aGet) : '';

        $iPagesCount = ceil($iCount / $iOnPage);
        $oPage = Page::getRootModule();
        if ($iPagesCount > 1) {
            if (!$bHideCanonicalPagination) {
                if (RegionHelper::isInstallModuleRegion()) {
                    $sCanonical = RegionHelper::getCanonical(
                        Tree::getSectionAliasPath($iSectionId)
                    );
                } else {
                    $sCanonical = Site::httpDomain() . Tree::getSectionAliasPath($iSectionId);
                }

                // если каноникал не был задан ранее, то ставим дефолтный
                if (!ArrayHelper::getValue($oPage->getData(), 'canonical_pagination')) {
                    $oPage->setData('canonical_pagination', $sCanonical);
                }
            }
        } else {
            $oPage->setData('canonical_pagination', '');
        }
        //центральная активная страница
        $iCenterGroup = ceil($iOnGroup / 2) + 1;
        //первая страница
        $iCountFirst = ($iPage > $iCenterGroup) ? $iPage - $iCenterGroup : 0;
        $iCountFirst = (($iCount < $iCountFirst + $iOnGroup) && ($iPage > $iCenterGroup)) ? $iCount - $iOnGroup + 2 : $iCountFirst;

        if ($bUseItems) {
            if ($iPagesCount) {
                $aPages['itemsIsActive'] = true;

                for ($i = $iCountFirst; $i < $iCountFirst + $iOnGroup; ++$i) {
                    if ($i == $iPagesCount) {
                        break;
                    }

                    $aItem['title'] = $i + 1;
                    $aItem['page'] = $i + 1;
                    $aItem['isActive'] = (($i + 1) == $iPage) ? true : false;
                    $aPages['items'][] = $aItem;
                }// each item
            }
        }// if pages count

        if ($bUsePages) {
            $aPages['prevPage']['isActive'] = ($iPage > 1) ? true : false;
            $aPages['prevPage']['page'] = ($iPage > 1) ? $iPage - 1 : $iPage;
            $aPages['prevPage']['title'] = '<';

            $aPages['nextPage']['isActive'] = ($iPage < $iPagesCount) ? true : false;
            $aPages['nextPage']['page'] = ($iPage < $iPagesCount) ? $iPage + 1 : $iPage;
            $aPages['nextPage']['title'] = '>';
        }// use previos/next pageLinks

        if ($bUseEdges) {
            $aPages['firstPage']['isActive'] = ($iPage > 1) ? true : false;
            $aPages['firstPage']['page'] = 1;
            $aPages['firstPage']['title'] = \Yii::t('page', 'page_first');

            $aPages['lastPage']['isActive'] = ($iPage < $iPagesCount) ? true : false;
            $aPages['lastPage']['page'] = $iPagesCount;
            $aPages['lastPage']['title'] = \Yii::t('page', 'page_last');
        }// use edges Links

        return $aPages;
    }

    // func
}// class
