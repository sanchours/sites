<?php

namespace skewer\build\Adm\FAQ;

use skewer\base\orm\Query;
use skewer\base\section\Parameters;
use skewer\build\Adm\FAQ\models\Faq;
use skewer\build\Adm\FAQ\Seo as SeoFaq;
use skewer\components\search\Prototype;
use yii\helpers\StringHelper;

/** @property Faq $oEntity */
class Search extends Prototype
{
    /**
     * отдает имя идентификатора ресурса для работы с поисковым индексом
     *
     * @return string
     */
    public function getName()
    {
        return 'FAQ';
    }

    /** {@inheritdoc} */
    protected function grabEntity()
    {
        return Faq::findOne(['id' => $this->oSearchIndexRow->object_id]);
    }

    /** {@inheritdoc} */
    protected function checkEntity()
    {
        if (!$this->oEntity) {
            return false;
        }

        if (!$this->oEntity->content) {
            return false;
        }

        if (!$this->oEntity->hasStatusApproved()) {
            return false;
        }

        return true;
    }

    /** {@inheritdoc} */
    protected function getNewSectionId()
    {
        return $this->oEntity->parent;
    }

    /** {@inheritdoc} */
    protected function fillSearchRow()
    {
        $sText = $this->stripTags(sprintf('%s %s', $this->oEntity->content, $this->oEntity->answer));

        $this->oSearchIndexRow->text = $sText;
        $this->oSearchIndexRow->search_text = $sText;
        $this->oSearchIndexRow->search_title = $this->stripTags(StringHelper::truncate($this->stripTags($this->oEntity->content), 220, '...'));
        $this->oSearchIndexRow->language = Parameters::getLanguage($this->oEntity->parent);
        $this->oSearchIndexRow->href = $this->buildHrefSearchIndexRow();
        $this->oSearchIndexRow->modify_date = $this->oEntity->last_modified_date;

        $oSeoComponent = new SeoFaq($this->oEntity->id, $this->oSection->id, $this->oEntity->getAttributes());
        $this->fillSearchRowSeoData($this->oSearchIndexRow, $oSeoComponent);
    }

    public function restore()
    {
        $sql = "INSERT INTO search_index(`status`,`class_name`,`object_id`)  SELECT '0','{$this->getName()}',id  FROM faq";
        Query::SQL($sql);
    }

    /** {@inheritdoc} */
    protected function buildHrefSearchIndexRow()
    {
        return $this->oEntity->getUrl(true);
    }
}
