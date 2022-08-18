<?php

namespace skewer\components\catalog;

use skewer\base\ft;
use skewer\base\orm\Query;
use skewer\base\site\Layer;
use skewer\base\site\Page;
use skewer\base\site\Site;
use skewer\base\SysVar;
use skewer\build\Catalog\Goods\Search;
use skewer\build\Page\Text\Module;
use skewer\build\Tool\LeftList\Group;
use skewer\components\search\CmsSearchEvent;
use skewer\components\search\models\SearchIndex;
use yii\web\ServerErrorHttpException;

class Api
{
    /** Название группы (метки) каталожных языковых параметров */
    const LANG_GROUP_NAME = 'catalog';

    private static $iECommerce = false;

    /**
     * @return bool
     */
    public static function isIECommerce()
    {
        if (self::$iECommerce == false) {
            self::setIECommerce();
        }

        return self::$iECommerce;
    }

    /**
     * @param bool $iECommerce
     */
    private static function setIECommerce()
    {
        self::$iECommerce = SysVar::get('catalog.e_commerce');
    }

    /**
     * Ищем товар по полю.
     *
     * @param string $sFieldName
     * @param $mValue
     * @param $mCard
     *
     * @return bool|GoodsRow
     */
    public static function getByField($sFieldName, $mValue, $mCard)
    {
        if ($oModel = ft\Cache::get($mCard)) {
            $sCard = '';

            $sBaseCard = $oModel->getName();

            /* ищем поле в карточке */
            if ($oModel->hasField($sFieldName)) {
                if ($oModel->getType() == Card::TypeExtended) {
                    $sCard = $oModel->getTableName();
                } else {
                    $sCard = $oModel->getTableName();
                }
            }

            if (!$sCard && $oModel->getType() == Card::TypeExtended) {
                $oParentModel = ft\Cache::get($oModel->getParentId());
                if ($oParentModel->hasField($sFieldName)) {
                    $sCard = $oParentModel->getTableName();
                }
            }

            if (!$sCard) {
                return false;
            }

            /** Запрос на поиск товара */
            $row = Query::SelectFrom($sCard)
                ->fields($sCard . '.id')
                ->join('inner', 'c_goods', '', $sCard . '.id=base_id')
                ->on('base_id=parent')
                ->where($sFieldName, $mValue)
                ->getOne();

            if ($row) {
                return GoodsRow::get($row['id'], $sBaseCard);
            }
        }

        return false;
    }

    /**
     * Создание товара.
     *
     * @param $mCard
     *
     * @return GoodsRow
     */
    public static function createGoodsRow($mCard)
    {
        return GoodsRow::create($mCard);
    }

    /**
     * Выставление значений поля в 0 для всех товаров в базовой карточке.
     *
     * @param string $sFieldName
     */
    public static function disableAll($sFieldName = 'active')
    {
        $sCard = 'base_card'; //Card::getNameById( 1 );
        Query::UpdateFrom('co_' . $sCard)
            ->set($sFieldName, 0)
            ->get();
    }

    /**
     * Выставление значений поля в 0 для всех товаров карточки.
     *
     * @param $mCard
     * @param string $sFieldName
     */
    public static function disableByCard($mCard, $sFieldName = 'active')
    {
        if ($oModel = ft\Cache::get($mCard)) {
            $sCard = '';
            $extCard = $oModel->getName();

            /* ищем поле в карточке */
            if ($oModel->hasField($sFieldName)) {
                if ($oModel->getType() == Card::TypeExtended) {
                    $sCard = $oModel->getTableName();
                } else {
                    $sCard = $oModel->getTableName();
                }
            }

            if ($oModel->getType() == Card::TypeExtended) {
                $oParentModel = ft\Cache::get($oModel->getParentId());
                $typeCard = 'ext_card_name';
                if (!$sCard) {
                    if ($oParentModel->hasField($sFieldName)) {
                        $sCard = $oParentModel->getTableName();
                    }
                }
            } else {
                $typeCard = 'base_card_name';
            }

            if ($sCard) {
                //Обновляем
                $sQuery = '
                    UPDATE `' . $sCard . '`
                    SET `' . $sFieldName . '`=0
                    WHERE `id` IN (SELECT `base_id` FROM `c_goods` WHERE `' . $typeCard . '` = :card)
                ;';

                Query::SQL($sQuery, ['card' => $extCard]);
            }
        }
    }

    /**
     * Запросник для выборки товаров с пустым значением поля $sFieldName.
     *
     * @param $sFieldName
     *
     * @return \skewer\base\orm\state\StateSelect
     */
    public static function selectAllFromField($sFieldName)
    {
        $sCard = 'base_card'; // Card::getNameById( 1 );
        return Query::SelectFrom('co_' . $sCard)
            ->fields('id')
            ->where($sFieldName, 0)
            ->asArray();
    }

    /**
     * Запросник для выборки товаров карточки $mCard с пустым значением поля $sFieldName.
     *
     * @param $sFieldName
     * @param $mCard
     *
     * @throws ServerErrorHttpException
     *
     * @return \skewer\base\orm\state\StateSelect
     */
    public static function selectFromField($sFieldName, $mCard)
    {
        if ($oModel = ft\Cache::get($mCard)) {
            $sExtCard = '';
            $bUseExtCard = false;

            if ($oModel->getType() == Card::TypeExtended) {
                $oParentModel = ft\Cache::get($oModel->getParentId());
                $sBaseCard = $oParentModel->getTableName();
                $sExtCard = $oModel->getTableName();
                $bUseExtCard = true;
            } else {
                $sBaseCard = $oModel->getTableName();
            }

            // выборка по таблицам
            $sFieldLine = 'DISTINCT ' . $sBaseCard . '.*, base_id';

            $oQuery = Query::SelectFrom($sBaseCard)
                ->join('inner', 'c_goods', '', $sBaseCard . '.id=base_id')
                ->on('base_id=parent');

            if ($bUseExtCard) {
                $oQuery->join('inner', $sExtCard, 'ext_card', 'ext_card.id=base_id');
                $sFieldLine .= ', ext_card.*';
            }

            $oQuery->fields($sFieldLine);

            return $oQuery->where($sFieldName, 0)->asArray();
        }

        throw new ServerErrorHttpException("No card `{$mCard}`");
    }

    /**
     * Удаление товара.
     *
     * @param $id
     *
     * @return bool
     */
    public static function deleteGoods($id)
    {
        $oSearch = new Search();
        $oSearch->deleteByObjectId($id);

        /** @var $GoodsRow */
        $GoodsRow = GoodsRow::get($id);

        if ($GoodsRow) {
            return $GoodsRow->delete();
        }

        return false;
    }

    /**
     * Список полей сео-группы.
     *
     * @return array
     */
    public static function getSeoFields()
    {
        $oSeoGroup = Card::getGroupByName('seo');
        if ($oSeoGroup) {
            $aFields = $oSeoGroup->getFields();
            if ($aFields) {
                $aRes = [];
                foreach ($aFields as $aField) {
                    /* @var $aField \skewer\components\catalog\model\FieldRow */
                    $aRes[] = $aField->name;
                }

                return $aRes;
            }
        }

        return [];
    }

    /** Деактивировать товары-аналоги (модификации) из поискового индекса */
    public static function deactiveModificationsFromSearch()
    {
        $sSearchTable = SearchIndex::tableName();
        $sGoodsTable = model\GoodsTable::getTableName();
        $sClassName = Search::CLASS_NAME;

        \Yii::$app->db->createCommand("
            UPDATE `{$sSearchTable}` AS A
                JOIN `{$sGoodsTable}` AS B
                ON (A.class_name = '{$sClassName}') AND (B.parent <> B.base_id) AND (B.base_id = A.object_id)
            SET `status` = 0
        ")->execute();
    }

    public static function getFieldsByCard($card, $attr = [])
    {
    }

    /**
     * Returns the fully qualified name of this class.
     *
     * @return string the fully qualified name of this class
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * Выполняет поиск товаров в CMS.
     *
     * @param CmsSearchEvent $oSearchEvent
     */
    public static function search(CmsSearchEvent $oSearchEvent)
    {
        $query = $oSearchEvent->query;

        $sCard = 'co_' . Card::DEF_BASE_CARD;

        /** Запрос на поиск товара */
        $aRowList = Query::SelectFrom($sCard)
            ->fields(['id', 'title', 'article'])
            ->join('inner', 'c_goods', '', $sCard . '.id=base_id')
            ->on('base_id=parent')
            ->where($sCard . '.title LIKE ?', '%' . $query . '%')
            ->orWhere($sCard . '.alias LIKE ?', '%' . $query . '%')
            ->orWhere($sCard . '.article LIKE ?', '%' . $query . '%')
            ->limit($oSearchEvent->getLimit())
            ->getAll();

        foreach ($aRowList as $aRow) {
            $oGoods = GoodsRow::get($aRow['id']);

            // если можно вывести в разделе
            if ($oGoods and $oGoods->getMainSection()) {
                $oSearchEvent->addRow([
                    'title' => sprintf(
                        '%s: %s (%s) [id=%d, section=%d]',
                        \Yii::$app->register->getModuleConfig('Goods', Layer::CATALOG)->getTitle(),
                        $aRow['title'],
                        $aRow['article'],
                        $aRow['id'],
                        $oGoods->getMainSection()
                    ),
                    'url' => Site::admTreeUrl($oGoods->getMainSection(), 'Catalog', 'section', $aRow['id'], 'content'),
                ]);
            } else {
                $oSearchEvent->addRow([
                    'title' => sprintf(
                        '%s: %s (%s) [id=%d]',
                        \Yii::$app->register->getModuleConfig('Goods', Layer::CATALOG)->getTitle(),
                        $aRow['title'],
                        $aRow['article'],
                        $aRow['id']
                    ),
                    'url' => Site::admUrl('Goods', 'catalog', $aRow['id']),
                ]);
            }
        }
    }

    /**
     * Проверка существования класса.
     *
     * @param string $sFieldType тип отображения
     * @param bool $bReturnDefClass - возвращать дефолтный класс StringField ??
     *
     * @return bool|string $sClassName- путь к классу
     */
    public static function getClassField($sFieldType, $bReturnDefClass = false)
    {
        $sFieldType = ($sFieldType == 'string') ? $sFieldType . 'Field' : $sFieldType;

        $sClassName = __NAMESPACE__ . '\\field\\' . ucfirst($sFieldType);

        if (!class_exists($sClassName)) {
            if ($bReturnDefClass) {
                $sClassName = __NAMESPACE__ . '\\field\\' . 'StringField';
            } else {
                return false;
            }
        }

        if (!is_subclass_of($sClassName, __NAMESPACE__ . '\\field\\' . 'Prototype')) {
            return false;
        }

        return $sClassName;
    }

    /**
     * Очистка текстовых блоков на страницах пагинатора >2.
     *
     * @param mixed $iPage
     */
    public static function removeTextContent($iPage)
    {
        $bTextPagin = SysVar::get('catalog.show_text_pagin');
        $oPage = Page::getRootModule();
        if (!$bTextPagin && $iPage > 1) {
            foreach ($oPage->processes as $oProcess) {
                $aParams = $oProcess->getParams();
                if (isset($aParams['layout']) && ($aParams['layout'] == Group::CONTENT) && Module::className() == $oProcess->getModuleClass()) {
                    $oPage->removeChildProcess($oProcess->getLabel());
                }
            }
        }
    }

    public static function getMaxPriority($parentId)
    {
        $aLastPriority = \Yii::$app->getDb()->createCommand(
            '
            SELECT MAX(`modific_priority`)
            FROM c_goods 
            WHERE `parent` =' . $parentId
        )->query()->read();

        $aLastPriority = (int) reset($aLastPriority) + 1;

        return $aLastPriority;
    }
}
