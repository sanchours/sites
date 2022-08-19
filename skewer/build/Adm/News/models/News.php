<?php

namespace skewer\build\Adm\News\models;

use skewer\base\router\Router;
use skewer\base\section\Tree;
use skewer\base\site\Site;
use skewer\build\Adm\News\Exporter;
use skewer\build\Adm\News\Importer;
use skewer\build\Adm\News\Search;
use skewer\build\Tool\Rss;
use skewer\build\Tool\SeoGen\exporter\GetListExportersEvent;
use skewer\build\Tool\SeoGen\importer\GetListImportersEvent;
use skewer\components\ActiveRecord\ActiveRecord;
use skewer\components\gallery\Album;
use skewer\components\seo\Api;
use skewer\components\seo\Service;
use skewer\helpers\Html;
use skewer\helpers\ImageResize;
use skewer\helpers\Transliterate;
use Yii;
use yii\base\ModelEvent;
use yii\base\UserException;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/**
 * This is the model class for table "news".
 *
 * @property int $id
 * @property string $news_alias
 * @property int $parent_section
 * @property string $publication_date
 * @property string $title
 * @property string $announce
 * @property string $Avtor
 * @property string $full_text
 * @property int $active
 * @property int $on_main
 * @property array|int $gallery
 * @property string $hyperlink
 * @property string $source_link
 * @property string $last_modified_date
 * @property string $url
 * @property string $format_announce
 *
 * @method static News findOne($condition)
 */
class News extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_section', 'title'], 'required'],
            [['parent_section', 'active', 'on_main', 'gallery'], 'integer'],
            [['publication_date', 'last_modified_date'], 'safe'],
            [['announce', 'full_text', 'Avtor'], 'string'],
            [['news_alias', 'title', 'hyperlink', 'source_link'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('news', 'field_id'),
            'news_alias' => Yii::t('news', 'field_alias'),
            'parent_section' => Yii::t('news', 'field_parent'),
            'publication_date' => Yii::t('news', 'field_date'),
            'title' => Yii::t('news', 'field_title'),
            'announce' => Yii::t('news', 'field_preview'),
            'Avtor' => Yii::t('news', 'field_Avtor'),
            'full_text' => Yii::t('news', 'field_fulltext'),
            'active' => Yii::t('news', 'field_active'),
            'on_main' => Yii::t('news', 'field_onmain'),
            'gallery' => Yii::t('news', 'field_gallery'),
            'hyperlink' => Yii::t('news', 'field_hyperlink'),
            'source_link' => Yii::t('news', 'field_source_link'),
            'last_modified_date' => Yii::t('news', 'field_modifydate'),
        ];
    }

    public static function getPublicNewsByAliasAndSec($sNewsAlias, $idSection)
    {
        return News::findOne(['news_alias' => $sNewsAlias, 'parent_section' => $idSection]);
    }

    public static function getPublicNewsById($iNewsId)
    {
        return News::findOne(['id' => $iNewsId]);
    }

    public function getFormat_Announce()
    {
        return str_replace('data-fancybox-group="button"', 'data-fancybox-group="news' . $this->id . '"', $this->announce);
    }

    /**
     * Creates data provider instance with search query applied.
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public static function getPublicList($params)
    {
        $query = News::find()->andFilterWhere(['active' => 1])->orderBy([
                                                                            'publication_date' => ($params['order'] == 'DESC') ? SORT_DESC : SORT_ASC,
                                                                        ]);

        if (!$params['on_main'] && Yii::$app->getRequest()->getQueryParam('per-page')) {
            $params['on_page'] = Yii::$app->getRequest()->getQueryParam('per-page');
        }

        $dataProvider = new ActiveDataProvider([
               'query' => $query,
               'pagination' => [
                   'pageSize' => (isset($params['on_page'])) ? $params['on_page'] : 10,
                   'page' => $params['page'] - 1,
               ],
           ]);

        if (isset($params['on_page'])) {
            // добавим рулесы в UrlManager для правильного построителя ЧПУ
            // нужно для коректной генерации пагинатора
            News::routerRegister();
        }

        /* Если есть фильтр по дате */
        if (isset($params['byDate']) && !empty($params['byDate'])) {
            $query->andFilterWhere(['>', 'publication_date', $params['byDate'] . ' 00:00:00']);
            $query->andFilterWhere(['<', 'publication_date', $params['byDate'] . ' 23:59:59']);
        }

        if (!$params['all_news'] && $params['section']) {
            $query->andFilterWhere(['parent_section' => $params['section']]);
        }

        if ($params['on_main']) {
            $query->andFilterWhere(['on_main' => 1]);
        }

        $aSections = Tree::getAllSubsection(\Yii::$app->sections->languageRoot());
        $query->andFilterWhere(['parent_section' => array_intersect_key($aSections, Tree::getVisibleSections())]);

        if ($params['future']) {
            $query->andFilterWhere(['>', 'publication_date', date('Y-m-d H:i:s', time())]);
        }

        return $dataProvider;
    }

    public static function routerRegister()
    {
        $url = Yii::$app->getRequest()->getPathInfo();
        $url = preg_replace('/page(.)*/', '${2}', $url);

        Yii::$app->getUrlManager()->addRules([$url . 'page/<page:[\w\.]+>' => 'site/index']);
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new News();

        $oRow->title = \Yii::t('news', 'new_news');
        $oRow->publication_date = date('Y-m-d H:i:s', time());
        $oRow->active = 1;

        $oRow->announce = '';
        $oRow->Avtor = '';
        $oRow->full_text = '';
        $oRow->gallery = 0;
        $oRow->hyperlink = '';
        $oRow->on_main = 0;

        if ($aData) {
            $oRow->setAttributes($aData);
        }

        return $oRow;
    }

    public function initSave()
    {
        if (!$this->news_alias) {
            $sValue = Transliterate::change($this->title);
        } else {
            $sValue = Transliterate::change($this->news_alias);
        }

        // приводим к нужному виду
        $sValue = Transliterate::changeDeprecated($sValue);
        $sValue = Transliterate::mergeDelimiters($sValue);
        $sValue = trim($sValue, '-');

        // к числам прибавляем префикс
        if (is_numeric($sValue)) {
            $sValue = 'news-' . $sValue;
        }

        try {
            $this->news_alias = Service::generateAlias($sValue, $this->id, $this->parent_section, 'News');
        } catch (UserException $e) {
            $this->addErrors(['news_alias' => $e->getMessage()]);

            return false;
        }

        // format wyswyg fields
        if ($this->full_text && $this->parent_section) {
            $this->full_text = ImageResize::wrapTags($this->full_text, $this->parent_section);
        }

        if ($this->announce && $this->parent_section) {
            $this->announce = ImageResize::wrapTags($this->announce, $this->parent_section);
        }

        if ($this->Avtor && $this->parent_section) {
            $this->Avtor = ImageResize::wrapTags($this->Avtor, $this->parent_section);
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

        $this->last_modified_date = date('Y-m-d H:i:s', time());

        return parent::initSave();
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $oSearch = new Search();
        $oSearch->updateByObjectId($this->id);

        if (($changedAttributes) && !(isset($changedAttributes['on_main']) && (count($changedAttributes) == 1))) {
            if (in_array($this->parent_section, Rss\Api::getSectionsIncludedInRss())) {
                Yii::$app->trigger(Rss\Api::EVENT_REBUILD_RSS);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        parent::afterDelete();

        // удаление SEO данных
        Api::del('news', $this->id);

        Album::removeAlbum($this->gallery);

        $oSearch = new Search();
        $oSearch->deleteByObjectId($this->id);

        Yii::$app->router->updateModificationDateSite();

        if (in_array($this->parent_section, Rss\Api::getSectionsIncludedInRss())) {
            Yii::$app->trigger(Rss\Api::EVENT_REBUILD_RSS);
        }
    }

    /**
     * Удаление всех новостей для раздела.
     *
     * @param ModelEvent $event
     */
    public static function removeSection(ModelEvent $event)
    {
        self::deleteAlbumsBySectionId($event->sender->id);

        self::deleteAll(['parent_section' => $event->sender->id]);
    }

    /**
     * Класс для сборки списка автивных поисковых движков.
     *
     * @param \skewer\components\search\GetEngineEvent $event
     */
    public static function getSearchEngine(\skewer\components\search\GetEngineEvent $event)
    {
        $event->addSearchEngine(Search::className());
    }

    /**
     * Возвращает максимальную дату модификации сущности.
     *
     * @return array|bool
     */
    public static function getMaxLastModifyDate()
    {
        return (new \yii\db\Query())->select('MAX(`last_modified_date`) as max')->from(self::tableName())->one();
    }

    /**
     * Вернет урл новости.
     *
     * @return string
     */
    public function getUrl()
    {
        if ($this->hyperlink) {
            return $this->hyperlink;
        }
        $hrefParam = $this->news_alias ? "news_alias={$this->news_alias}" : "news_id={$this->id}";

        return "[{$this->parent_section}][News?" . $hrefParam . ']';
    }

    /**
     * Обрезает текст аннонса до указанной длины
     * @param null $textLength
     * @return string
     */
    public function getTruncateAnnounce($textLength = null)
    {
        if (empty($textLength)) {
            return $this->announce;
        }

        return StringHelper::truncate($this->announce, (int)$textLength, ' ...');
    }

    /**
     * Новость имеет ссылку на детальную страницу?
     *
     * @return bool
     */
    public function hasDetailLink()
    {
        return Html::hasContent($this->full_text) || $this->hyperlink;
    }

    /**
     * Ведет ли ссылка на внешний ресурс
     *
     * @return bool
     */
    public function isExternalHyperLink(): bool
    {
        if (empty($this->hyperlink)) {
            return false;
        }

        $domainParams = parse_url(Site::httpDomain());
        $hyperlinkParams = parse_url($this->hyperlink);

        if (isset($domainParams['host']) && isset($hyperlinkParams['host']) && $domainParams['host'] !== $hyperlinkParams['host']) {
            return true;
        }

        return false;
    }

    /**
     * Набивает внутренний массив события $oEvent последними новостями.
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
            ->where('announce <> :emptyString', ['emptyString' => ''])
            ->andWhere(['active' => 1])
            ->andWhere(['parent_section' => $aSections])
            ->orderBy(['publication_date' => SORT_DESC])
            ->limit(Rss\Api::COUNT_RECORDS_PER_MODULE)
            ->all();

        $oEvent->aRows = ArrayHelper::merge($oEvent->aRows, $aRecords);
    }

    /**
     * Удалит альбомы новостей по id раздела.
     *
     * @param int $iSectionId - id раздела
     */
    private static function deleteAlbumsBySectionId($iSectionId)
    {
        $oQuery = News::find()
            ->where(['parent_section' => $iSectionId]);

        foreach ($oQuery->each() as $oNew) {
            Album::removeAlbum($oNew->gallery);
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

    /**
     * Возвращает html ссылки на предварительный просмотр
     * @return string
     */
    public function getPreviewLink(): string
    {
        $sLinkHtml = '';
        $sItemUrl = Router::rewriteURL($this->getUrl());
        $sUrlText = \Yii::t('news', 'field_preview_url');
        if (!$this->id) {
            $sLinkHtml = \Yii::t('news', 'preview_no_save_error');
        } elseif (!Tree::isSectionVisible($this->parent_section)) {
            $sLinkHtml = \Yii::t('news', 'preview_visible_error');
        } else {
            if ($this->hyperlink) {
                $sLinkHtml = \Yii::t('news', 'preview_hyperlink_error');
                $sLinkHtml .= "<br>";
                $sLinkHtml .= "<a href='$this->hyperlink' target='_blank'>$sUrlText</a>";
            } else {
                $sLinkHtml .= "<a href='$sItemUrl' target='_blank'>$sUrlText</a>";
            }
        }
        return $sLinkHtml;
    }
}
