<?php

namespace skewer\components\catalog;

use skewer\base\ft;
use skewer\base\orm\Query;
use skewer\build\Catalog\Collections\SeoElementCollection;
use skewer\components\catalog\field\Prototype;
use yii\helpers\ArrayHelper;

/**
 * Класс для выборки значений для простых сущностей
 * Class ObjectSelector.
 */
class ObjectSelector extends SelectorPrototype
{
    /**
     * Получение объекта.
     *
     * @param int|string $card Идентификатор сущности
     * @param int $id Идентификатор объекта
     *
     * @throws \Exception
     * @throws ft\Exception
     *
     * @return array
     */
    public static function get($id, $card)
    {
        $oModel = ft\Cache::get($card);

        if (!$oModel) {
            throw new \Exception('Не найдена модель для карточки.');
        }
        $query = Query::SelectFrom($oModel->getTableName(), $oModel);

        if (is_numeric($id)) {
            $query->where('id', $id);
        } else {
            $query->where('alias', $id);
        }

        $row = $query->asArray()->getOne();

        if (!$row) {
            return false;
        }

        return Parser::get($oModel->getFileds())->object($row + ['card' => $oModel->getName()]);
    }

    /**
     * Получить позицию следующую за заданной.
     *
     * @param int $id Ид объекта
     * @param int|string $card Карточка вывода
     * @param array $filter Фильтр для сортировки
     *
     * @return GoodsRow
     */
    public static function getPrev($id, $card, /** @noinspection PhpUnusedParameterInspection */
                                   $filter = [])
    {
        $oModel = ft\Cache::get($card);
        if (!$oModel) {
            return false;
        }

        $row = Query::SelectFrom($oModel->getTableName())
            ->where('id < ?', $id)
            ->where('active', 1)
            ->order('id', 'DESC')
            ->asArray()
            ->getOne();

        if (!$row) {
            $row = Query::SelectFrom($oModel->getTableName())
                ->where('id > ?', $id)
                ->where('active', 1)
                ->order('id', 'DESC')
                ->asArray()
                ->getOne();
        }

        if (!$row) {
            return false;
        }

        return Parser::get($oModel->getFileds())->object($row + ['card' => $oModel->getName()]);
    }

    /**
     * Получить позицию идущую перед заданным
     *
     * @param int $id Ид объекта
     * @param int|string $card Карточка вывода
     * @param array $filter Фильтр для сортировки
     *
     * @return GoodsRow
     */
    public static function getNext($id, $card, /** @noinspection PhpUnusedParameterInspection */
                                   $filter = [])
    {
        $oModel = ft\Cache::get($card);
        if (!$oModel) {
            return false;
        }

        $row = Query::SelectFrom($oModel->getTableName())
            ->where('id > ?', $id)
            ->where('active', 1)
            ->order('id', 'ASC')
            ->asArray()
            ->getOne();

        if (!$row) {
            $row = Query::SelectFrom($oModel->getTableName())
                ->where('id < ?', $id)
                ->where('active', 1)
                ->order('id', 'ASC')
                ->asArray()
                ->getOne();
        }

        if (!$row) {
            return false;
        }

        return Parser::get($oModel->getFileds())->object($row + ['card' => $oModel->getName()]);
    }

    /**
     * Список коллекций.
     *
     * @param string $card Карточка коллекции
     * @param int $field
     *
     * @return ObjectSelector
     */
    public static function getCollections($card, /** @noinspection PhpUnusedParameterInspection */
                                          $field = 0)
    {
        $oGoods = new self();

        $oGoods->selectCard($card);
        $oGoods->initParser();

        $oGoods->oQuery = Query::SelectFrom('cd_' . $oGoods->sBaseCard);

        $oGoods->oQuery->asArray();

        $oGoods->bSorted = false;

        return $oGoods;
    }

    /**
     * Получить элемент коллекции.
     *
     * @param $iElemId - id элемента коллекции
     * @param $mCard - id | alias коллеккции
     * @param int $iSectionId - id текущего раздела. Если =0 - вернет эл.коллекции без seo данных
     *
     * @throws \Exception
     *
     * @return array
     */
    public static function getElementCollection($iElemId, $mCard, $iSectionId = 0)
    {
        $aData = self::get($iElemId, $mCard);

        // seo - данные
        if ($iSectionId and $aData) {
            $aData = self::addSeoDataInCollection($aData, $mCard, $iSectionId);
        }

        return $aData;
    }

    public function parse()
    {
        $list = $this->oQuery->getAll();

        $aItems = [];
        foreach ($list as $row) {
            $aItems[] = $this->oParser->object($row + ['card' => $this->sBaseCard]);
        }

        // seo - данные
        if ($this->bWithSeo && ($iSectionId = $this->getInnerParam('iSectionId'))) {
            foreach ($aItems as &$aItem) {
                $sCard = ArrayHelper::getValue($aItem, 'card', '');
                $aItem = self::addSeoDataInCollection($aItem, $sCard, $iSectionId);
            }
        }

        return $aItems;
    }

    /**
     * Добавляет в массив seo данные элемента коллекции.
     *
     * @param array $aData - данные элемента коллекции
     * @param mixed $mCard - карточка коллекции
     * @param int $iSection - id текущего раздела
     *
     * @throws \Exception
     *
     * @return $this
     */
    private static function addSeoDataInCollection($aData, $mCard, $iSection)
    {
        if (!$mCard) {
            throw new \Exception('Не передана карточка');
        }
        $sCard = is_numeric($mCard) ? Card::getName($mCard) : $mCard;

        $oSeo = new SeoElementCollection($aData['id'], $iSection, $aData, $sCard);

        foreach ($aData['fields'] as $field) {
            $sNameFieldClass = Api::getClassField($field['type']);
            if ($sNameFieldClass) {
                /** @var Prototype $oProtField */
                $oProtField = new $sNameFieldClass();
                $oProtField->setSeo($oSeo, $field, $iSection);
            }
        }

        return $aData;
    }
}
