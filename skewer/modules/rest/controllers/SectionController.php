<?php

namespace skewer\modules\rest\controllers;

use skewer\base\router\Router;
use skewer\base\section\models\ParamsAr;
use skewer\base\section\models\TreeSection;
use skewer\components\auth\Auth;

/**
 * Работа с разделами через rest
 * Class SectionController.
 */
class SectionController extends PrototypeController
{
    /* @var string Базовый набор выбираемый полей раздела */
    const BASIC_FIELDS = "id, title, visible, alias_path, parent, '' AS `category_img`, '' AS `image_mobile`";

    /** Получить детальную информацию раздела */
    public function actionView($id)
    {
        // Проверка прав на запрашиваемый раздел
        if (($aDenySections = Auth::getDenySections('public')) and
              in_array($id, $aDenySections)) {
            return '';
        }

        /* @var $oSection \yii\db\ActiveRecord */
        $aSection = TreeSection::find()
            ->select(self::BASIC_FIELDS . ", '' AS `text`")
            ->where(['id' => $id])
            ->asArray()
            ->one();

        if (!$aSection) {
            return [];
        }

        // Получить картинки раздела
        $aParams = ParamsAr::find()
            ->select(['name', 'value', 'show_val'])
            ->where(['parent' => $id, 'name' => ['category_img', 'image_mobile']])
            ->indexBy('name')
            ->asArray()
            ->all();

        //Получить текст раздела
        $aParamsText = ParamsAr::find()
            ->select(['group', 'value', 'show_val'])
            ->where(['parent' => $id, 'group' => 'staticContent', 'name' => 'source'])
            ->indexBy('group')
            ->asArray()
            ->all();

        $aSection['text'] = Router::rewriteURLs($aParamsText['staticContent']['show_val'] ?? '');
        $aSection['category_img'] = (isset($aParams['category_img']['value']) and $aParams['category_img']['value']) ? $this->getImages((int) $aParams['category_img']['value']) : '';
        $aSection['image_mobile'] = (isset($aParams['image_mobile']['value']) and $aParams['image_mobile']['value']) ? $this->getImages((int) $aParams['image_mobile']['value']) : '';

        return $aSection;
    }

    /** Получить список разделов по фильтру */
    public function actionIndex()
    {
        // (!) При успешной обработке запроса, но при отсутвии позиций, списковые методы должны отдавать пустой массив, а не строку

        /* @var int Фильтр по разделам */
        $iFilterId = (int) \Yii::$app->request->get('id');

        /* @var int Фильтр по родительскому разделу (если ни один фильтр не задан, то берутся корневые разделы (parent = 3) */
        $iFilterParentId = (int) \Yii::$app->request->get('parent') ?: ($iFilterId ? 0 : 3);

        /* @var array Массив всех разделов на одном уровне */
        $aAllSections = TreeSection::find()
            ->select(self::BASIC_FIELDS . ", '' AS `children`")
            ->where(['>', 'id', 3])
            ->andWhere(['NOT IN', 'id', Auth::getDenySections('public')])
            ->orderBy('level, position') // Внимание! первая сортировка по level обязательна иначе не верно соберётся массив $aSectionsFiltred
            ->indexBy('id')
            ->asArray()
            ->all();

        if (!$aAllSections) {
            return [];
        }

        /* @var array Дерево разделов (выходной массив массивов потомков) */
        $aSectionsTree = [];
        /* @var array Массив отфильтрованных id разделов */
        $aSectionsFiltred = [];

        // Расставить потомков разделам и собрать результирующее дерево разделов
        foreach ($aAllSections as $id => &$aSectionData) {
            // Собрать дерево согласно фильтру
            if (($iFilterId and ($id == $iFilterId)) or
                 ($iFilterParentId and ($aSectionData['parent'] == $iFilterParentId))) {
                $aSectionsTree[$id] = &$aSectionData;
            }

            // Записать id раздела, участвующего в выдаче
            if (isset($aSectionsTree[$id]) or isset($aSectionsFiltred[$aSectionData['parent']])) {
                $aSectionsFiltred[$id] = $id;
            }

            // Добавить потомка в раздел
            if (isset($aAllSections[$aSectionData['parent']])) {
                $aAllSections[$aSectionData['parent']]['children'][$id] = &$aSectionData;
            }
        }

        // Получить картинки к разделам
        $aParams = ParamsAr::find()
            ->select(['parent', 'name', 'value', 'show_val'])
            ->where(['parent' => $aSectionsFiltred, 'name' => ['category_img', 'image_mobile']])
            ->indexBy('value')
            ->asArray()
            ->all();

        if ($aParams) {
            $aSectionAlbumsIds = [];
            $aMobileAlbumsIds = [];

            foreach ($aParams as &$aParam) {
                switch ($aParam['name']) {
                    // Собрать id альбомов галерей разделов модуля CategoryViewer
                    case 'category_img':
                        if ($aParam['value']) {
                            $aSectionAlbumsIds[] = $aParam['value'];
                        }
                        break;

                    // Собрать id альбомов галерей разделов для обработки одним запросом чуть ниже
                    case 'image_mobile':
                        if ($aParam['value']) {
                            $aMobileAlbumsIds[] = $aParam['value'];
                        }
                        break;
                }
            }

            // Записать картинки разводки категорий к каждому разделу
            if ($aImages = $this->getImages($aSectionAlbumsIds, false)) {
                foreach ($aImages as $iAlbumId => &$aImage) {
                    $aAllSections[$aParams[$iAlbumId]['parent']]['category_img'] = &$aImage;
                }
            }

            // Записать картинки для мобильного приложения к каждому разделу
            if ($aImages = $this->getImages($aMobileAlbumsIds, false)) {
                foreach ($aImages as $iAlbumId => &$aImage) {
                    $aAllSections[$aParams[$iAlbumId]['parent']]['image_mobile'] = &$aImage;
                }
            }
        }

        return array_values($aSectionsTree);
    }
}
