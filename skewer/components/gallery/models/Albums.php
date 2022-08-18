<?php

namespace skewer\components\gallery\models;

use skewer\base\section\Parameters;
use skewer\base\section\params\Type;
use skewer\build\Adm\Gallery\Exporter;
use skewer\build\Adm\Gallery\Importer;
use skewer\build\Adm\Gallery\Search;
use skewer\build\Tool\SeoGen\exporter\GetListExportersEvent;
use skewer\build\Tool\SeoGen\importer\GetListImportersEvent;
use skewer\components\ActiveRecord\ActiveRecord;
use skewer\components\gallery\Album;
use skewer\components\seo\Service;
use skewer\helpers\Transliterate;
use Yii;
use yii\base\ModelEvent;
use yii\base\UserException;

/**
 * This is the model class for table "photogallery_albums".
 *
 * @property int $id
 * @property int $section_id
 * @property string $title
 * @property string $alias
 * @property string $description
 * @property string $creation_date
 * @property int $visible
 * @property int $priority
 * @property string $owner
 * @property int $profile_id
 * @property string $last_modified_date
 *
 * @method static Photos|null findOne($condition)
 */
class Albums extends ActiveRecord
{
    public function __construct()
    {
        parent::__construct();
        $this->title = \Yii::t('gallery', 'title_default');
        $this->visible = 1;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'photogallery_albums';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['section_id', 'profile_id'], 'required', 'message' => \Yii::t('gallery', 'general_field_empty')], // Служебные поля

            [['section_id', 'visible', 'priority', 'profile_id'], 'integer'],
            [['creation_date', 'last_modified_date'], 'safe'],
            [['title', 'owner'], 'string', 'max' => 100],
            [['alias'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 512],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('gallery', 'id'),
            'section_id' => \Yii::t('gallery', 'section_id'),
            'title' => \Yii::t('gallery', 'title'),
            'alias' => \Yii::t('gallery', 'alias'),
            'description' => \Yii::t('gallery', 'description'),
            'creation_date' => \Yii::t('gallery', 'creation_date'),
            'last_modified_date' => \Yii::t('gallery', 'last_modified_date'),
            'visible' => \Yii::t('gallery', 'visible'),
            'priority' => \Yii::t('gallery', 'priority'),
            'owner' => \Yii::t('gallery', 'owner'),
            'profile_id' => \Yii::t('gallery', 'profile_id'),
        ];
    }

    /**
     * Вернет урл альбома.
     *
     * @return string
     */
    public function getUrl()
    {
        $sAlias = ($this->alias) ? "alias={$this->alias}" : "id={$this->id}";

        return "[{$this->section_id}][Gallery?{$sAlias}]";
    }

    /**
     * Удаляет альбомы и изображения из раздела $iSectionId.
     *
     * @param ModelEvent $event
     */
    public static function removeSection(ModelEvent $event)
    {
        if ($aAlbums = \skewer\components\gallery\Album::getBySection($event->sender->id, false)) {
            foreach ($aAlbums as $aAlbum) {
                $mError = false;
                \skewer\components\gallery\Album::removeAlbum($aAlbum['id'], $mError);
            }
        }

        // Получить параметры удаляемого раздела
        $aParams = Parameters::getList($event->sender->id)
            ->fields(['access_level', 'value'])
            ->asArray()
            ->get();

        // Найти среди них галереи и удалить альбомы
        if ($aParams) {
            foreach ($aParams as $aParam) {
                if ((abs($aParam['access_level']) == Type::paramGallery) and $aParam['value']) {
                    Album::removeAlbum($aParam['value'], $mError);
                }
            }
        }
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
     * {@inheritdoc}
     */
    public function initSave()
    {
        if (!$this->alias) {
            $sValue = Transliterate::change($this->title);
        } else {
            $sValue = Transliterate::change($this->alias);
        }

        // приводим к нужному виду
        $sValue = Transliterate::changeDeprecated($sValue);
        $sValue = Transliterate::mergeDelimiters($sValue);
        $sValue = trim($sValue, '-');

        if (is_numeric($sValue)) {
            $sValue = 'album-' . $sValue;
        }

        try {
            $this->alias = Service::generateAlias($sValue, $this->id, $this->section_id, 'Gallery');
        } catch (UserException $e) {
            $this->addErrors(['alias' => $e->getMessage()]);

            return false;
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

        if ($insert) {
            $iCount = Album::getCountAlbumsBySection($this->section_id);
            if ($iCount == 2) {
                $oSearch->bResetAllAlbums = true;
            }
        }

        $oSearch->updateByObjectId($this->id);
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        parent::afterDelete();

        $oSearch = new Search();

        $iCount = Album::getCountAlbumsBySection($this->section_id);

        if ($iCount == 1) {
            $oSearch->resetBySectionId($this->section_id);
            Service::updateSearchIndex();
        }

        $oSearch->deleteByObjectId($this->id);
        Yii::$app->router->updateModificationDateSite();
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
