<?php

namespace skewer\build\Adm\Gallery;

use skewer\base\orm\Query;
use skewer\base\section\Parameters;
use skewer\base\section\Template;
use skewer\components\gallery;
use skewer\components\search\models\SearchIndex;
use skewer\components\search\Prototype;
use skewer\components\seo\Service;

/** @property gallery\models\Albums $oEntity  */
class Search extends Prototype
{
    /** @var bool Флаг необходимости сброса статуса всем альбомам */
    public $bResetAllAlbums = false;

    /**
     * отдает имя идентификатора ресурса для работы с поисковым индексом
     *
     * @return string
     */
    public function getName()
    {
        return 'Gallery';
    }

    /** {@inheritdoc} */
    protected function grabEntity()
    {
        return gallery\Album::getById($this->oSearchIndexRow->object_id);
    }

    /** {@inheritdoc} */
    protected function beforeUpdate()
    {
        if ($this->bResetAllAlbums) {
            $this->resetBySectionId($this->oEntity->section_id);
            Service::updateSearchIndex();
        }
    }

    /** {@inheritdoc} */
    protected function doSupportSearchIndex($iObjectId)
    {
        $oAlbum = gallery\Album::getById($iObjectId);

        if (!$oAlbum) {
            return false;
        }

        if ($oAlbum->owner === 'entity') {
            return false;
        }

        return true;
    }

    /** {@inheritdoc} */
    protected function checkEntity()
    {
        if (!$this->oEntity) {
            return false;
        }

        if (!$this->oEntity->visible) {
            return false;
        }

        if (!gallery\Photo::getCountByAlbum($this->oEntity->id)) {
            return false;
        }

        if (Parameters::getTpl($this->oEntity->section_id) != Template::getTemplateIdForModule('Gallery')) {
            return false;
        }

        // Если стоит галочка выводить только фото
        if (Parameters::getValByName($this->oEntity->section_id, 'content', 'openAlbum')) {
            return false;
        }

        $iCountAlbums = gallery\Album::getCountAlbumsBySection($this->oEntity->section_id);

        if ($iCountAlbums <= 1) {
            return false;
        }

        return true;
    }

    /** {@inheritdoc} */
    protected function getNewSectionId()
    {
        return $this->oEntity->section_id;
    }

    /** {@inheritdoc} */
    protected function fillSearchRow()
    {
        $sText = $this->stripTags($this->oEntity->description);

        $this->oSearchIndexRow->text = $sText;
        $this->oSearchIndexRow->search_text = $sText;
        $this->oSearchIndexRow->search_title = $this->stripTags($this->oEntity->title);
        $this->oSearchIndexRow->modify_date = $this->oEntity->last_modified_date;
        $this->oSearchIndexRow->language = Parameters::getLanguage($this->oSearchIndexRow->section_id);
        $this->oSearchIndexRow->href = $this->buildHrefSearchIndexRow();

        $oSeoComponent = new Seo($this->oEntity->id, $this->oSearchIndexRow->section_id, $this->oEntity->getAttributes());
        $this->fillSearchRowSeoData($this->oSearchIndexRow, $oSeoComponent);
    }

    /** {@inheritdoc} */
    protected function buildHrefSearchIndexRow()
    {
        $sURL = \Yii::$app->router->rewriteURL(sprintf(
            '[%s][Gallery?%s=%s]',
            $this->oEntity->section_id,
            'alias',
            $this->oEntity->alias
        ));

        return $sURL;
    }

    /**
     * Сброс статуса для всех сущностей, привязанных к этому разделу.
     *
     * @param $id - id раздела
     */
    public function resetAllEntityBySectionId($id)
    {
        SearchIndex::updateAll(['status' => 0], ['section_id' => $id]);
    }

    /**
     *  воссоздает полный список пустых записей для сущности, отдает количество добавленных.
     */
    public function restore()
    {
        $sql = "INSERT INTO search_index(`status`,`class_name`,`object_id`) SELECT '0', '{$this->getName()}', id FROM photogallery_albums WHERE profile_id=6 AND owner!='entity'";
        Query::SQL($sql);
    }
}
