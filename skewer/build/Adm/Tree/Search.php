<?php

namespace skewer\build\Adm\Tree;

use skewer\base\orm\Query;
use skewer\base\section\models\TreeSection;
use skewer\base\section\Page;
use skewer\base\section\Parameters;
use skewer\base\section\Template;
use skewer\base\section\Tree;
use skewer\build\Page\Main;
use skewer\build\Page\Text\Api as TextApi;
use skewer\components\search\models\SearchIndex;
use skewer\components\search\Prototype;
use skewer\components\seo\Service;

/** @property TreeSection $oEntity */
class Search extends Prototype
{
    /**
     * Флаг необходимости рекурсивного сброса данных по дереву разделов.
     *
     * @var bool
     */
    protected $bRecursiveReset = false;

    /**
     * отдает имя идентификатора ресурса для работы с поисковым индексом
     *
     * @return string
     */
    public function getName()
    {
        return 'Page';
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleTitle()
    {
        return \Yii::t('page', 'tab_name');
    }

    /** {@inheritdoc} */
    protected function checkSection()
    {
        if (!parent::checkSection()) {
            return false;
        }

        /* Исключаем корневые разделы языковых версий */
        if (in_array($this->oSection->id, \Yii::$app->sections->getValues(Page::LANG_ROOT))) {
            return false;
        }

        // отсечь все ветви дерева, кроме основной (3)
        if (!in_array(\Yii::$app->sections->root(), Tree::getSectionParents($this->oSection->id))) {
            return false;
        }

        return true;
    }

    /** {@inheritdoc}*/
    protected function fillSearchRow()
    {
        $sText = TextApi::getTextContentFromZone($this->oSearchIndexRow->object_id);
        $sTitle = Tree::getSectionsTitle($this->oSearchIndexRow->object_id);

        $this->oSearchIndexRow->language = Parameters::getLanguage($this->oSearchIndexRow->object_id);
        $this->oSearchIndexRow->href = $this->buildHrefSearchIndexRow();
        $this->oSearchIndexRow->text = $this->stripTags($sText);
        $this->oSearchIndexRow->search_text = $this->stripTags($sText);
        $this->oSearchIndexRow->search_title = $this->stripTags($sTitle);
        $this->oSearchIndexRow->modify_date = $this->oSection->last_modified_date;

        $oSeoComponent = new Main\Seo($this->oSection->id, $this->oSection->id, $this->oSection->attributes + ['text' => $sText]);
        $this->fillSearchRowSeoData($this->oSearchIndexRow, $oSeoComponent);
    }

    /** {@inheritdoc} */
    protected function buildHrefSearchIndexRow()
    {
        return \Yii::$app->router->rewriteURL('[' . $this->oSearchIndexRow->object_id . ']');
    }

    /**
     * {@inheritdoc}
     */
    protected function buildHasRealUrl()
    {
        return $this->oEntity->hasRealUrl();
    }

    /**
     * Сброс индекса разделов, созданных на основе шаблона $iTplId
     * и записей сущностей, принадлежащих этим разделам.
     *
     * @param $iTplId - id шаблона
     */
    public function resetSectionAndEntitiesByTemplate($iTplId)
    {
        $aSections = Template::getSubSectionsByTemplate($iTplId);

        if ($aSections) {
            SearchIndex::updateAll(['status' => 0], ['section_id' => $aSections]);
        }
    }

    /**
     * @param $id
     * рекурсивный сброс индекса раздела и подчиненных ему
     */
    protected function resetSectionRecursive($id)
    {
        // сбросить статус для сущностей, привязанных к этому разделу
        SearchIndex::updateAll(['status' => 0], ['section_id' => $id]);

        // найти подразделы
        $section = Tree::getSection($id);
        if (!$section) {
            return;
        }
        $aSubSections = $section->getSubSections();

        // выполнить сброс для подразделов
        foreach ($aSubSections as $item) {
            $this->resetToId($item->id);
            $this->resetSectionRecursive($item->id);
        }
    }

    /**
     *  воссоздает полный список пустых записей для сущности, отдает количество добавленных.
     */
    public function restore()
    {
        $sql = "INSERT INTO search_index(`status`,`class_name`,`object_id`)  SELECT '0','{$this->getName()}',id  FROM tree_section WHERE parent>3";
        Query::SQL($sql);
    }

    /**
     * Устанавливает флаг рекурсивного сброса поискового индекса по дереву разделов.
     */
    public function setRecursiveResetFlag()
    {
        $this->bRecursiveReset = true;
    }

    /** {@inheritdoc} */
    protected function grabEntity()
    {
        return Tree::getSection($this->oSearchIndexRow->object_id);
    }

    /** {@inheritdoc} */
    protected function checkEntity()
    {
        // Считаем, что сущность прошла проверку. Реально будем проверять в checkSection
        return true;
    }

    /** {@inheritdoc} */
    protected function grabSection()
    {
        return $this->oEntity;
    }

    /** {@inheritdoc} */
    protected function beforeUpdate()
    {
        if ($this->bRecursiveReset) {
            $this->resetSectionRecursive($this->oSearchIndexRow->object_id);
            Service::updateSearchIndex();
        }

        // Это шаблон?
        if (($this->oEntity->parent == \Yii::$app->sections->templates())) {
            $this->resetSectionAndEntitiesByTemplate($this->oEntity->id);
            Service::updateSearchIndex();
        }
    }

    /** {@inheritdoc} */
    public function getNewSectionId()
    {
        return $this->oSearchIndexRow->object_id;
    }

    /** {@inheritdoc} */
    public function deleteByObjectId($iId)
    {
        if ($this->bRecursiveReset) {
            $this->resetSectionRecursive($iId);
            Service::updateSearchIndex();
        }

        parent::deleteByObjectId($iId);
    }
}
