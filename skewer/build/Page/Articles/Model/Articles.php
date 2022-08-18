<?php

namespace skewer\build\Page\Articles\Model;

use skewer\base\ft;
use skewer\base\orm;
use skewer\base\section\Tree;
use skewer\build\Adm\Articles\Exporter;
use skewer\build\Adm\Articles\Importer;
use skewer\build\Tool\Rss;
use skewer\build\Tool\SeoGen\exporter\GetListExportersEvent;
use skewer\build\Tool\SeoGen\importer\GetListImportersEvent;
use skewer\components\gallery\Album;
use yii\base\ModelEvent;
use yii\helpers\ArrayHelper;

class Articles extends orm\TablePrototype
{
    protected static $sTableName = 'articles';

    protected static function initModel()
    {
        ft\Entity::get('articles')
            ->clear()
            ->setTablePrefix('')
            ->setNamespace(__NAMESPACE__)
            ->addField('articles_alias', 'varchar(255)', 'articles.field_alias')
            ->addField('parent_section', 'varchar(255)', 'articles.field_parent')
            ->addField('author', 'varchar(255)', 'articles.field_author')
            ->addField('publication_date', 'datetime', 'articles.field_date')
            ->addField('gallery', 'int(11)', 'articles.field_gallery')
            ->setDefaultVal('now')
            ->addField('title', 'varchar(255)', 'articles.field_title')
            ->addField('announce', 'text', 'articles.field_preview')
            ->addField('full_text', 'text', 'articles.field_fulltext')
            ->addField('active', 'int(1)', 'articles.field_active')
            ->addField('archive', 'int(1)', 'articles.field_archive')
            ->addField('on_main', 'int(1)', 'articles.field_on_main')
            ->addField('hyperlink', 'varchar(255)', 'articles.field_hyperlink')
            ->addField('source_link', 'varchar(255)', 'articles.field_source_link')
            ->addModificator('link')
            ->addField('last_modified_date', 'date', 'articles.field_modifydate')
            ->save()
            //->build()
;
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new ArticlesRow();

        $oRow->title = \Yii::t('articles', 'new_article');
        $oRow->publication_date = date('Y-m-d H:i:s', time());
        $oRow->active = 1;

        if ($aData) {
            $oRow->setData($aData);
        }

        return $oRow;
    }

    /**
     * Метод по выборке списка статей из базы.
     *
     * @static
     *
     * @param int $iPage Страница для показа
     * @param array $aParams Параметры для фильтрации
     * @param int $iCnt Параметры для фильтрации
     *
     * @return ArticlesRow[]
     */
    public static function getPublicList($iPage, $aParams, &$iCnt = 0)
    {
        $oQuery = self::find()
            ->where('active', 1)
            ->order('publication_date', $aParams['order'])
            ->limit((int) $aParams['on_page'], ($iPage - 1) * $aParams['on_page']);

        /* Если есть фильтр по дате */
        if (isset($aParams['byDate']) && !empty($aParams['byDate'])) {
            $oQuery->where('publication_date BETWEEN ?', [$aParams['byDate'] . ' 00:00:00', $aParams['byDate'] . ' 23:59:59']);
        }

        if (!$aParams['all_articles'] && $aParams['section']) {
            $oQuery->where('parent_section', $aParams['section']);
        }

        $aSections = Tree::getAllSubsection(\Yii::$app->sections->languageRoot());
        $oQuery->where('parent_section', array_intersect_key($aSections, Tree::getVisibleSections()));

        if ($aParams['on_main']) {
            $oQuery->where('on_main', 1);
        }

        if ($aParams['future']) {
            $oQuery->where('publication_date > ?', date('Y-m-d H:i:s', time()));
        }

        $aItems = $oQuery->setCounterRef($iCnt)->getAll();

        //$aItems = Mapper::getItems($aFilter);

        /** @var ArticlesRow $oItem */
        foreach ($aItems as &$oItem) {
            $oItem->announce = str_replace(
                'data-fancybox-group="button"',
                'data-fancybox-group="articles' . $oItem->id . '"',
                $oItem->announce
            );

            $oItem->publication_date = date('d.m.Y', strtotime($oItem->publication_date));
        }

        return $aItems;
    }

    public static function getPublicById($iArticlesId)
    {
        return self::find($iArticlesId);
    }

    public static function getPublicByAliasAndSec($sAlias, $idSection)
    {
        return self::find()->where('articles_alias', $sAlias)->andWhere('parent_section', $idSection)->getOne();
    }

    /**
     * Удаление статей принадлежащих разделу.
     *
     * @param ModelEvent $event
     *
     * @throws \Exception
     */
    public static function removeSection(ModelEvent $event)
    {
        self::deleteAlbumsBySectionId($event->sender->id);

        $sQuery = sprintf(
            'DELETE FROM `%s` WHERE `parent_section`=:section;',
            static::$sTableName
        );

        orm\Query::SQL(
            $sQuery,
            ['section' => $event->sender->id]
        )->affectedRows();
    }

    /**
     * Класс для сборки списка автивных поисковых движков.
     *
     * @param \skewer\components\search\GetEngineEvent $event
     */
    public static function getSearchEngine(\skewer\components\search\GetEngineEvent $event)
    {
        $event->addSearchEngine(\skewer\build\Adm\Articles\Search::className());
    }

    /**
     * Возвращает максимальную дату модификации сущности.
     *
     * @return array|bool
     */
    public static function getMaxLastModifyDate()
    {
        return (new \yii\db\Query())->select('MAX(`last_modified_date`) as max')->from(self::$sTableName)->one();
    }

    /**
     * Набивает внутренний массив события $oEvent последними статьями.
     *
     * @param Rss\GetRowsEvent $oEvent
     */
    public static function getRssRows(Rss\GetRowsEvent $oEvent)
    {
        $aSections = array_intersect(Tree::getVisibleSections(), Rss\Api::getSectionsIncludedInRss());

        if (!$aSections) {
            return;
        }

        $aRecords = self::find()
            ->where('active', 1)
            ->where('parent_section', $aSections)
            ->where('announce<>?', '')
            ->order('publication_date', 'DESC')
            ->limit(Rss\Api::COUNT_RECORDS_PER_MODULE)
            ->getAll();

        $oEvent->aRows = ArrayHelper::merge($oEvent->aRows, $aRecords);
    }

    /**
     * Удалит альбомы статей по id раздела.
     *
     * @param int $iSectionId - id раздела
     */
    private static function deleteAlbumsBySectionId($iSectionId)
    {
        $oQuery = Articles::find()
            ->where(['parent_section' => $iSectionId]);

        /** @var ArticlesRow $row */
        while ($row = $oQuery->each()) {
            Album::removeAlbum($row->gallery);
        }
    }

    /**
     * Регистрирует класс Importer, в списке импортёров события $oEvent.
     *
     * @param GetListImportersEvent $oEvent
     */
    public static function getImporter(GetListImportersEvent $oEvent)
    {
        $oEvent->addImporter(Importer::className());
    }

    /**
     * Регистрирует класс Exporter, в списке экпортёров события $oEvent.
     *
     * @param GetListExportersEvent $oEvent
     */
    public static function getExporter(GetListExportersEvent $oEvent)
    {
        $oEvent->addExporter(Exporter::className());
    }
}
