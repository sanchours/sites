<?php

namespace skewer\modules\rest\controllers;

use skewer\base\SysVar;
use skewer\base\Twig;
use skewer\components\catalog\GoodsSelector;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Работа с каталогом через rest
 * Class CatalogController.
 */
class CatalogController extends PrototypeController
{
    /** @var string Набор полей для выдачи детальной информации товара (без пробелов) */
    private static $sDetailFields = 'id,title,article,section,price,currency,announce,obj_description,gallery';

    /** @var string Набор полей для выдачи списка товара (без пробелов) */
    private static $sListFields = 'id,title,article,price,currency,announce,gallery';

    /** @var bool $aFormatted Форматировать значения полей? */
    private static $aFormatted = '';

    public function actionView($id)
    {

        // добавочные поля
        $sAddFields = trim(\Yii::$app->request->get('add_fields'), ',');

        self::$aFormatted = trim(\Yii::$app->request->get('formatted'));
        $aGood = GoodsSelector::get($id, 'base_card');
        if (!$aGood) {
            return '';
        }

        // Если = * то получить все поля каталожного объекта
        if ($sAddFields)
            self::$sDetailFields .= ($sAddFields == '*') ? "," . join(',', array_keys($aGood['fields'])) : ",$sAddFields";

        $aListFields = $aFields = array_flip(explode(',', self::$sDetailFields));

        $aFields['related'] = $this->getRelatedList($id, $aListFields);
        $aFields['modification'] = $this->getModificationList($id, $aListFields);

        return $aFields;
    }

    public function actionIndex()
    {
        // (!) При успешной обработке запроса, но при отсутвии позиций, списковые методы должны отдавать пустой массив, а не строку
        // (!) Заголовки постраничника нужно отдавать всегда при успешной обработке запроса

        if (!\Yii::$app->register->moduleExists('Goods', 'Catalog'))
            throw new NotFoundHttpException();

        $sSortField = \Yii::$app->request->get('sort');

        $iPage = abs((int)\Yii::$app->request->get('page', 0)) ?: 1;
        $iOnPage = abs((int)\Yii::$app->request->get('onpage', 10));
        ($iOnPage <= 100) or ($iOnPage = 100); // Ограничить до 100 позиций на одной странице

        $iSection = \Yii::$app->request->get('section', 0);
        $sTitleFilter = \Yii::$app->request->get('title', '');
        $sFieldFilter = \Yii::$app->request->get('show-type', '');

        // добавочные поля (если = *, то получить все поля объекта)
        $sAddFields = trim(\Yii::$app->request->get('add_fields'), ',');
        self::$aFormatted = trim(\Yii::$app->request->get('formatted'));

        if ($iPage < 1) $iPage = 1;

        $sSortWay = 'up';
        if (strpos($sSortField, '-') === 0) {
            $sSortWay = 'down';
            $sSortField = substr($sSortField, 1);
        }

        $query = ($iSection) ? GoodsSelector::getList4Section($iSection) : GoodsSelector::getList();

        $query->parseAllActiveFields()->condition('active', true);

        if ($sTitleFilter)
            $query->condition('title LIKE ?', '%' . $sTitleFilter . '%');

        if ($sFieldFilter)
            $query->condition($sFieldFilter);

        if (in_array($sSortField, ['price', 'title']))
            $query->sort($sSortField, ($sSortWay == 'down') ? 'DESC' : 'ASC');

        $iTotalCount = 0;
        $query->limit($iOnPage, $iPage, $iTotalCount);
        $aGoods = $query->parse();
        $this->setPagination($iTotalCount, ceil($iTotalCount / $iOnPage), $iPage, $iOnPage);
        if (!$aGoods) {
            return [];
        }

        if ($sAddFields)
            self::$sListFields .= ($sAddFields == '*') ? "," . join(',', array_keys($aGoods[0]['fields'])) : ",$sAddFields";

        $aFields = array_flip(explode(',', self::$sListFields));

        $list = [];

        foreach ($aGoods as $item) {
            $list[] = self::parseFields($aFields, $item, true);
        }

        return $list;
    }

    /**
     * Обработать экспортируемые поля
     *
     * @param array &$aFields Набор полей
     * @param array &$aData Значения полей
     * @param bool $bFirstImg Брать только первое изображение галереи
     * @return array
     */
    public static function parseFields(&$aFields, &$aData, $bFirstImg = false)
    {
        foreach ($aFields as $sField => &$mVal) {
            /** Оригинальное значение без форматирования*/
            $mOriginVal = '';
            $aFormatted = explode(',', self::$aFormatted);

            switch ($sField) {
                case 'section':
                    $sField = 'main_section';
                    break;
                case 'id':
                    $mVal = $aData[$sField];
                    break;

                case 'currency':
                    $mVal = ArrayHelper::getValue($aData, 'fields.price.attrs.measure', '');
                    break;
                case 'price':
                    if (is_array($aFormatted) && in_array($sField, $aFormatted)) {
                        $mVal = trim(strip_tags(ArrayHelper::getValue($aData, "fields.$sField.html", '')));
                        $mVal = Twig::priceFormat($mVal,false);
                        $mOriginVal = Twig::priceFormat(ArrayHelper::getValue($aData, "fields.$sField.value", ''),false);
                    } else {
                        $mVal = ArrayHelper::getValue($aData, "fields.$sField.value", '');
                    }

                    break;

                default:
                    if (isset($aData['fields'][$sField]['type']) and ($aData['fields'][$sField]['type'] == 'gallery')) {
                        $mVal = $bFirstImg ? ArrayHelper::getValue($aData, "fields.{$sField}.gallery.images.0.images_data", '') : ArrayHelper::getValue($aData, "fields.{$sField}.gallery.images", '');
                    } else {
                        $mVal = (in_array($sField, $aFormatted)) ? ArrayHelper::getValue($aData, "fields.{$sField}.html", '') : ArrayHelper::getValue($aData, "fields.{$sField}.value", '');
                        $mOriginVal = ArrayHelper::getValue($aData, "fields.{$sField}.value", '');
                    }
            }

            if (self::$aFormatted) {
                if (in_array($sField, $aFormatted)) {
                    $mVal = [
                        'title' => ArrayHelper::getValue($aData, "fields.{$sField}.title", '') ?: $sField,
                        'value' => ($mVal) ? (is_array($mVal) ? $mVal : "{$mVal}") : '', // Здесь всегда должна возвращаться либо строка либо массив. Не числовой тип.
                        'origin' => ($mOriginVal !== '') ? $mOriginVal : $mVal,
                    ];
                }
            }
        }

        return $aFields;
    }

    /**
     * Возвращает список модификаций для товара
     * @param int $id
     * @param array $aListFields
     * @return array
     * @throws \skewer\components\catalog\Exception
     */
    private function getModificationList($id, $aListFields)
    {
        $aResult = [];
        if (SysVar::get('catalog.goods_modifications')) {
            $aModificationList = GoodsSelector::getModificationList($id)
                ->condition('active', 1)
                ->parse();

            if ($aModificationList) {
                foreach ($aModificationList as $aValue) {
                    $aFields = $aListFields;
                    $aResult[] = self::parseFields($aFields, $aValue);
                }
            }
        }

        return $aResult;
    }

    /**
     * Возвращает список сопутствующих для товара
     * @param int $id
     * @param array $aListFields
     * @return array
     */
    private function getRelatedList($id, $aListFields)
    {
        $aResult = [];

        if (SysVar::get('catalog.goods_related')) {
            $aRelatedList = GoodsSelector::getRelatedList($id)
                ->parse();

            if ($aRelatedList) {
                foreach ($aRelatedList as $aValue) {
                    $aFields = $aListFields;
                    $aResult[] = self::parseFields($aFields, $aValue);
                }
            }
        }

        return $aResult;
    }
}
