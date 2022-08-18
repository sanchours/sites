<?php

namespace skewer\build\Adm\News;

use skewer\base\orm\Query;
use skewer\base\section\Parameters;
use skewer\build\Adm\News\models\News;
use skewer\components\search\Prototype;

/** @property News oEntity */
class Search extends Prototype
{
    /**
     * отдает имя идентификатора ресурса для работы с поисковым индексом
     *
     * @return string
     */
    public function getName()
    {
        return 'News';
    }

    /** {@inheritdoc} */
    protected function grabEntity()
    {
        return News::findOne(['id' => $this->oSearchIndexRow->object_id]);
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

        $oSeoComponent = new Seo($this->oEntity->id, $this->oSection->id, $this->oEntity->getAttributes());
        $this->fillSearchRowSeoData($this->oSearchIndexRow, $oSeoComponent);
    }

    /** {@inheritdoc} */
    protected function buildHrefSearchIndexRow()
    {
        if (!empty($this->oEntity->news_alias)) {
            $sHref = \Yii::$app->router->rewriteURL(sprintf(
                '[%s][News?news_alias=%s]',
                $this->oEntity->parent_section,
                $this->oEntity->news_alias
            ));
        } else {
            $sHref = \Yii::$app->router->rewriteURL(sprintf(
                '[%s][News?id=%s]',
                $this->oEntity->parent_section,
                $this->oEntity->id
            ));
        }

        return $sHref;
    }

    /**
     *  воссоздает полный список пустых записей для сущности, отдает количество добавленных.
     *
     * @return int
     */
    public function restore()
    {
        $sql = "INSERT INTO search_index(`status`,`class_name`,`object_id`)  SELECT '0','{$this->getName()}',id  FROM news";
        Query::SQL($sql);
    }
}
