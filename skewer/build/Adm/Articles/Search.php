<?php

namespace skewer\build\Adm\Articles;

use skewer\base\orm\Query;
use skewer\base\section\Parameters;
use skewer\build\Page\Articles\Model\Articles;
use skewer\build\Page\Articles\Model\ArticlesRow;
use skewer\components\search\Prototype;

/** @property ArticlesRow $oEntity */
class Search extends Prototype
{
    /**
     * отдает имя идентификатора ресурса для работы с поисковым индексом
     *
     * @return string
     */
    public function getName()
    {
        return 'Articles';
    }

    /** {@inheritdoc} */
    protected function grabEntity()
    {
        return Articles::find($this->oSearchIndexRow->object_id);
    }

    /** {@inheritdoc} */
    protected function checkEntity()
    {
        if (!$this->oEntity) {
            return false;
        }

        if (!$this->oEntity->full_text) {
            return false;
        }

        if (!$this->oEntity->active) {
            return false;
        }

        return true;
    }

    /** {@inheritdoc} */
    protected function getNewSectionId()
    {
        return $this->oEntity->parent_section;
    }

    /** {@inheritdoc} */
    protected function fillSearchRow()
    {
        $sText = $this->stripTags($this->oEntity->full_text);

        $this->oSearchIndexRow->text = $sText;
        $this->oSearchIndexRow->search_text = $sText;
        $this->oSearchIndexRow->search_title = $this->stripTags($this->oEntity->title);
        $this->oSearchIndexRow->language = Parameters::getLanguage($this->oEntity->parent_section);
        $this->oSearchIndexRow->href = $this->buildHrefSearchIndexRow();
        $this->oSearchIndexRow->modify_date = $this->oEntity->last_modified_date;

        $oSeoComponent = new Seo($this->oEntity->id, $this->oSection->id, $this->oEntity->getData());
        $this->fillSearchRowSeoData($this->oSearchIndexRow, $oSeoComponent);
    }

    /** {@inheritdoc} */
    protected function buildHrefSearchIndexRow()
    {
        $sUrl = \Yii::$app->router->rewriteURL(sprintf(
            '[%s][Articles?%s=%s]',
            $this->oEntity->parent_section,
            $this->oEntity->articles_alias ? 'articles_alias' : 'articles_id',
            $this->oEntity->articles_alias ? $this->oEntity->articles_alias : $this->oEntity->id
        ));

        return $sUrl;
    }

    /**
     *  воссоздает полный список пустых записей для сущности, отдает количество добавленных.
     *
     * @return int
     */
    public function restore()
    {
        $sql = "INSERT INTO search_index(`status`,`class_name`,`object_id`)  SELECT '0','{$this->getName()}',id  FROM articles";
        Query::SQL($sql);
    }
}
