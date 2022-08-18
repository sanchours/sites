<?php

namespace skewer\build\Page\Articles\Model;

use skewer\base\orm;
use skewer\base\router\Router;
use skewer\base\section\Tree;
use skewer\build\Adm\Articles\Search;
use skewer\build\Tool\Rss;
use skewer\components\gallery\Album;
use skewer\components\seo\Api;
use skewer\components\seo\Service;
use skewer\helpers\Html;
use skewer\helpers\ImageResize;
use skewer\helpers\Transliterate;
use yii\base\UserException;

class ArticlesRow extends orm\ActiveRecord
{
    public $id = 0;
    public $articles_alias = '';
    public $parent_section = 0;
    public $author = '';
    public $publication_date = '';
    /** @var array|string */
    public $gallery = '';
    public $title = 'articles.new_article';
    public $announce = '';
    public $full_text = '';
    public $active = 1;
    public $archive = 0;
    public $on_main = 0;
    public $hyperlink = '';
    public $source_link = '';
    public $last_modified_date = '';

    public function __construct()
    {
        $this->setTableName('articles');
        $this->setPrimaryKey('id');
    }

    /**
     * Вернет урл статьи.
     *
     * @return string
     */
    public function getUrl()
    {
        $hrefParam = ($this->articles_alias) ? "articles_alias={$this->articles_alias}" : "articles_id={$this->id}";

        return  ($this->hyperlink) ? $this->hyperlink : ("[{$this->parent_section}][Articles?" . $hrefParam . ']');
    }

    /**
     * Возвращает html ссылки на предварительный просмотр
     * @return string
     */
    public function getPreviewLink(): string
    {
        $sLinkHtml = '';
        $sItemUrl = Router::rewriteURL($this->getUrl());
        $sUrlText = \Yii::t('articles', 'field_preview_url');
        if (!$this->id) {
            $sLinkHtml = \Yii::t('articles', 'preview_no_save_error');
        } elseif (!Tree::isSectionVisible($this->parent_section)) {
            $sLinkHtml = \Yii::t('articles', 'preview_visible_error');
        } else {
            if ($this->hyperlink) {
                $sLinkHtml = \Yii::t('articles', 'preview_hyperlink_error');
                $sLinkHtml .= "<br>";
                $sLinkHtml .= "<a href='$this->hyperlink' target='_blank'>$sUrlText</a>";
            } else {
                $sLinkHtml .= "<a href='$sItemUrl' target='_blank'>$sUrlText</a>";
            }
        }
        return $sLinkHtml;
    }

    /**
     * Статья имеет ссылку на детальную страницу?
     *
     * @return bool
     */
    public function hasDetailLink()
    {
        return Html::hasContent($this->full_text) || $this->hyperlink;
    }

    /**
     * Форматирование данных перед сохранением
     */
    public function initSave()
    {
        if (mb_strlen($this->title) > 255) {
            $this->setFieldError('title', \Yii::t('articles', 'error_maxvalue_field_title', ['fieldName' => \Yii::t('articles', 'field_title'), 'maxValue' => 255]));

            return false;
        }

        // Генерация alias
        if (!$this->checkAlias()) {
            return false;
        }

        // last modification date
        $this->last_modified_date = date('Y-m-d H:i:s', time());

        // format wyswyg fields
        if ($this->full_text && $this->parent_section) {
            $this->full_text = ImageResize::wrapTags($this->full_text, $this->parent_section);
        }

        if ($this->announce && $this->parent_section) {
            $this->announce = ImageResize::wrapTags($this->announce, $this->parent_section);
        }

        $aFieldsLink = ['hyperlink', 'source_link'];

        foreach ($aFieldsLink as $item) {
            if (!empty($this->{$item}) && (mb_strpos($this->{$item}, 'http') === false) and !(in_array(mb_substr($this->{$item}, 0, 1), ['/', '[']))) {
                $this->{$item} = 'http://' . $this->{$item};
            }
        }

        if (!$this->publication_date || ($this->publication_date == 'null')) {
            $this->publication_date = date('Y-m-d H:i:s', time());
        }

        return parent::initSave();
    }

    /**
     * Генерация и проверка уникального псевдонима.
     *
     * @return bool
     */
    public function checkAlias()
    {
        // generate
        if (!$this->articles_alias) {
            $this->articles_alias = Transliterate::change($this->title);
        } else {
            $this->articles_alias = Transliterate::change($this->articles_alias);
        }

        $this->articles_alias = Transliterate::changeDeprecated($this->articles_alias);
        $this->articles_alias = Transliterate::mergeDelimiters($this->articles_alias);
        $this->articles_alias = trim($this->articles_alias, '-');

        // к числам прибавляем префикс
        if (is_numeric($this->articles_alias)) {
            $this->articles_alias = 'articles-' . $this->articles_alias;
        }

        try {
            $this->articles_alias = Service::generateAlias($this->articles_alias, $this->id, $this->parent_section, 'Articles');
        } catch (UserException $e) {
            $this->setFieldError('articles_alias', $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @throws \skewer\base\ui\ARSaveException
     *
     * @return bool|void
     */
    public function save()
    {
        $res = parent::save();

        $oSearch = new Search();
        $oSearch->updateByObjectId($this->id);

        if (in_array($this->parent_section, Rss\Api::getSectionsIncludedInRss())) {
            \Yii::$app->trigger(Rss\Api::EVENT_REBUILD_RSS);
        }

        return $res;
    }

    /**
     * Действия, выполняемые после удаления записи.
     *
     * @param self $oRow
     */
    public function afterDelete($oRow)
    {
        // удаление SEO данных
        Api::del('articles', $oRow->id);

        Album::removeAlbum($oRow->gallery);

        $search = new Search();
        $search->deleteByObjectId($oRow->id);
        \Yii::$app->router->updateModificationDateSite();

        if (in_array($this->parent_section, Rss\Api::getSectionsIncludedInRss())) {
            \Yii::$app->trigger(Rss\Api::EVENT_REBUILD_RSS);
        }

        parent::afterDelete($oRow);
    }
}
