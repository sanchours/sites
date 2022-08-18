<?php

namespace skewer\build\Catalog\Collections;

use skewer\base\ft;
use skewer\base\orm\ActiveRecord;
use skewer\base\orm\Query;
use skewer\base\section\Parameters;
use skewer\components\catalog;
use skewer\components\catalog\Parser;
use skewer\components\search\Prototype;
use yii\helpers\ArrayHelper;

/**
 * Поисковый движок для каталожных коллекций
 * Умеет работать со множеством коллеций.
 *
 * !При работе после инициализации требует вызова функции provideName() или setCard()
 *
 * @property ActiveRecord $oEntity - элемент коллекции
 */
class Search extends Prototype
{
    const NAME_PREFIX = 'CollectionViewer_';

    /** @var string имя карточки */
    private $sCard = '';

    /**
     * отдает имя идентификатора ресурса для работы с поисковым индексом
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getName()
    {
        if (!$this->sCard) {
            throw new \Exception('Card for Collection\Search is not set');
        }

        return self::NAME_PREFIX . $this->sCard;
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleTitle()
    {
        $sTitle = \Yii::t('collections', 'tab_name');

        $coll = $this->getCollection();

        if (!$coll) {
            return $sTitle;
        }

        $sTitle = sprintf('%s (%s)', $sTitle, $coll->title);

        return $sTitle;
    }

    /**
     * Отдает сущность коллекции.
     *
     * @return null|catalog\model\EntityRow
     */
    private function getCollection()
    {
        $c = catalog\Collection::getCollection($this->sCard);

        return $c ?: null;
    }

    /**
     * Класс для сборки списка автивных поисковых движков.
     *
     * @param \skewer\components\search\GetEngineEvent $event
     */
    public static function getSearchEngine(\skewer\components\search\GetEngineEvent $event)
    {
        foreach (catalog\Collection::getCollections() as $c) {
            $event->addSearchEngine(Search::className(), self::NAME_PREFIX . $c->name);
        }
    }

    /**
     * Задает имя карточки для коллекции.
     *
     * @param $sCardName
     */
    public function setCard($sCardName)
    {
        $this->sCard = $sCardName;
    }

    /**
     * {@inheritdoc}
     */
    public function provideName($sName)
    {
        parent::provideName($sName);
        // вычисляем имя карточки
        $this->setCard(str_replace(self::NAME_PREFIX, '', $sName));
    }

    /** {@inheritdoc} */
    protected function fillSearchRow()
    {
        $title = ArrayHelper::getValue($this->oEntity, 'title', '');
        $alias = ArrayHelper::getValue($this->oEntity, 'alias', '');
        $last_modified_date = ArrayHelper::getValue($this->oEntity, 'last_modified_date', '');

        $sText = trim(
            $this->stripTags(ArrayHelper::getValue($this->oEntity, 'info', '')) . ' ' .
            $this->stripTags($alias)
        );

        $this->oSearchIndexRow->text = $sText;
        $this->oSearchIndexRow->search_text = $sText;
        $this->oSearchIndexRow->search_title = $this->stripTags($title);
        $this->oSearchIndexRow->href = $this->buildHrefSearchIndexRow();
        $this->oSearchIndexRow->language = Parameters::getLanguage($this->oSearchIndexRow->section_id);
        $this->oSearchIndexRow->modify_date = $last_modified_date;

        $oSeoComponent = new SeoElementCollection($this->oSearchIndexRow->object_id, $this->oEntity->getModel()->getEntityId(), $this->oEntity);

        $this->fillSearchRowSeoData($this->oSearchIndexRow, $oSeoComponent);
    }

    /** {@inheritdoc} */
    protected function buildHrefSearchIndexRow()
    {
        $alias = ArrayHelper::getValue($this->oEntity, 'alias', '');

        return Parser::buildUrl($this->oSearchIndexRow->section_id, $this->oSearchIndexRow->object_id, $alias);
    }

    /** {@inheritdoc} */
    protected function grabEntity()
    {
        $coll = $this->getCollection();

        if (!$coll) {
            return false;
        }

        // ищем элемент коллекции
        $oTable = ft\Cache::getMagicTable($coll->name);
        if (!$oTable) {
            return false;
        }

        return $oTable->find($this->oSearchIndexRow->object_id);
    }

    /** {@inheritdoc} */
    protected function checkEntity()
    {
        if (!ArrayHelper::getValue($this->oEntity, 'active', true)) {
            return false;
        }

        return true;
    }

    /** {@inheritdoc} */
    protected function getNewSectionId()
    {
        //Определить ид раздела по ид коллекции
        $iSectionId = catalog\Section::get4Collection($this->oEntity->getModel()->getEntityId());

        return $iSectionId;
    }

    /**
     *  воссоздает полный список пустых записей для сущности, отдает количество добавленных.
     *
     * @return int
     */
    public function restore()
    {
        $sql = "INSERT INTO search_index(`status`,`class_name`,`object_id`) SELECT '0','" . $this->getName() . "',id  FROM cd_{$this->sCard}";
        Query::SQL($sql);
    }
}
