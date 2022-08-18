<?php

namespace skewer\build\Adm\GuestBook\models;

use skewer\base\ft\exception\Query as ExceptionQuery;
use skewer\base\orm\Query;
use skewer\base\router\Router;
use skewer\base\section\Parameters;
use skewer\build\Page\CatalogViewer;
use skewer\build\Page\GuestBook\Module;
use skewer\components\ActiveRecord\ActiveRecord;
use skewer\components\catalog\GoodsRow;
use skewer\components\catalog\model\GoodsTable;
use skewer\components\gallery\Photo;
use skewer\components\rating\Rating;
use yii\base\ModelEvent;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "guest_book".
 *
 * @property int $id
 * @property int $rating_id
 * @property int $parent
 * @property string $parent_class
 * @property string $date_time
 * @property string $name
 * @property string $email
 * @property string $content
 * @property int $status
 * @property string $city
 * @property int $on_main
 * @property string $last_modified_date
 * @property int $photo_gallery
 * @property string $company
 */
class GuestBook extends ActiveRecord
{
    /** статус "новый" */
    const statusNew = 0;

    /** статус "одобрен" */
    const statusApproved = 1;

    /** статус "отклонен" */
    const statusRejected = 2;

    /** вывод в карусель "выводить" */
    const carouselApproved = 1;

    /** Класс отзывов к товарам */
    const GoodReviews = 'GoodsReviews';

    /**
     * Базовый набор виртуальных полей для экспорта на клиентскую часть.
     *
     * @return array
     */
    public function fields()
    {
        return ArrayHelper::merge(parent::fields(), [
            'photo_gallery' => 'ImagesDataGallery',
            'date_time' => 'FormattingDateTime',
        ]);
    }

    /**
     * Расширенный набор виртуальных полей для экспорта модели на клиентскую часть.
     *
     * @return array
     */
    public function extraFields()
    {
        return [
            'link' => 'linkWithAnchor',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'guest_book';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent'], 'required'],
            [['rating_id', 'parent', 'status', 'on_main', 'photo_gallery'], 'integer'],
            [['date_time', 'last_modified_date'], 'safe'],
            [['content'], 'string'],
            [['parent_class'], 'string', 'max' => 20],
            [['name', 'email'], 'string', 'max' => 128],
            [['city', 'company'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rating_id' => 'Rating ID',
            'parent' => 'Parent',
            'parent_class' => 'Parent Class',
            'date_time' => 'Date Time',
            'name' => 'Name',
            'email' => 'Email',
            'content' => 'Content',
            'status' => 'Status',
            'city' => 'City',
            'on_main' => 'On Main',
            'last_modified_date' => 'Last Modified Date',
            'photo_gallery' => 'Photo Gallery',
            'company' => 'Company',
        ];
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        try {
            Query::startTransaction();
            $sObjectRating = ($this->isGoodReviews()) ? CatalogViewer\Module::getNameModule() : Module::getNameModule();
            $oldRating = $this->getOldAttribute('rating_id');
            if ($this->rating_id && $this->rating_id != $oldRating) {
                $oRate = new Rating($sObjectRating);
                if (!$oldRating) {
                    $oRating = $oRate->createNewRate($this->parent, $this->rating_id);
                } else {
                    $oRating = $oRate->getRatingById($oldRating);
                    $oRating->rate = $this->rating_id;
                }
                $bSuccess = $oRating->save();
                if (!$bSuccess) {
                    throw new ExceptionQuery('Не сохранена базовая запись');
                }
                $this->rating_id = $oRating->id;
            }

            if (!parent::save($runValidation, $attributeNames)) {
                throw new ExceptionQuery('Не сохранена базовая запись');
            }
            Query::commitTransaction();
        } catch (ExceptionQuery $e) {
            Query::rollbackTransaction();

            return false;
        }

        return true;
    }

    /**
     * Получить объект новой строки.
     *
     * @param array $aData - данные дял установки
     *
     * @return GuestBook
     */
    public static function getNewRow($aData = [])
    {
        $oRow = new self();

        $oRow->rating_id = (isset($aData['rating'])) ? $aData['rating'] : 0;
        $oRow->parent = 0;
        $oRow->parent_class = '';
        $oRow->date_time = date('Y-m-d H:i:s', time());
        $oRow->name = '';
        $oRow->email = '';
        $oRow->content = '';
        $oRow->status = self::statusNew;
        $oRow->city = '';
        $oRow->on_main = 0;
        $oRow->last_modified_date = '';
        $oRow->photo_gallery = 0;
        $oRow->company = '';

        if ($aData) {
            $oRow->setAttributes($aData);
        }

        return $oRow;
    }

    /**
     * Действия выполняемые до сохранения и проверки валидации
     * даже если save() вызван без проверки валидации( save(false) ).
     */
    public function initSave()
    {
        $this->rating_id = (int) $this->rating_id;
        $this->parent = (int) $this->parent;
        $this->status = (int) $this->status;
        $this->on_main = (int) $this->on_main;
        $this->photo_gallery = (int) $this->photo_gallery;

        if (!$this->date_time || ($this->date_time == 'null')) {
            $this->date_time = date('Y-m-d H:i:s', time());
        }

        $this->last_modified_date = date('Y-m-d H:i:s', time());

        return parent::initSave();
    }

    /**
     * Действия после удаления.
     */
    public function afterDelete()
    {
        \Yii::$app->router->updateModificationDateSite();

        $this->removeRating();

        return parent::afterDelete();
    }

    /**
     * Получить ссылку с якорем на отзыв.
     *
     * @return string
     */
    public function getLinkWithAnchor()
    {
        $iNumberPage = $this->getNumberPagePagination();

        if ($this->isSectionReviews()) {
            if ($iNumberPage == 1) {
                $sLink = sprintf('[%d]', $this->parent);
            } else {
                $sLink = sprintf('[%d][GuestBook?page=%d]', $this->parent, $iNumberPage);
            }
        } else {
            // Основной раздел товара
            $aGoodData = GoodsTable::get($this->parent);
            $iBaseSection = (int) ArrayHelper::getValue($aGoodData, 'section');

            // alias товара
            $oGoodRow = GoodsRow::get($this->parent);
            $sAlias = ArrayHelper::getValue($oGoodRow->getData(), 'alias');

            if ($iNumberPage == 1) {
                $sLink = sprintf('[%d][CatalogViewer?goods-alias=%s&tab=reviews]', $iBaseSection, $sAlias);
            } else {
                $sLink = sprintf('[%d][CatalogViewer?goods-alias=%s&tab=reviews&page=%d]', $iBaseSection, $sAlias, $iNumberPage);
            }
        }

        $sRewriteLink = Router::rewriteURL($sLink) . '#' . $this->id;

        return $sRewriteLink;
    }

    /**
     * Получить данные об изображения галереи отзыва.
     *
     * @return array|false
     */
    public function getImagesDataGallery()
    {
        $sImagesData = false;

        if ($this->photo_gallery) {
            $photos = Photo::getFromAlbum($this->photo_gallery, true);
            $oFirstImage = $photos[0] ?? false;
            if ($oFirstImage) {
                $sImagesData = $oFirstImage->images_data;
            }
        }

        return $sImagesData;
    }

    /**
     * Получить оформатированную дату отзыва.
     *
     * @return string
     */
    public function getFormattingDateTime()
    {
        return date('d.m.Y', strtotime($this->date_time));
    }

    /**
     * Получить номер страницы пагинатора, на которой отображается отзыв.
     *
     * @return int
     */
    private function getNumberPagePagination()
    {
        $iCount = GuestBook::find()
            ->where(['status' => GuestBook::statusApproved])
            ->andWhere(['parent_class' => $this->parent_class])
            ->andWhere(['parent' => $this->parent])
            ->andWhere(['>', 'date_time', $this->date_time])
            ->count('id');

        if ($this->isSectionReviews()) {
            $iOnPage = Parameters::getValByName($this->parent, 'content', 'onPageContent', true);

            if (!$iOnPage) {
                $iOnPage = Parameters::getValByName($this->parent, 'content', 'onPage', true);
            }
        } else {
            $iOnPage = Module::onPageGoodsReviews;
        }

        if (!$iOnPage) {
            $iOnPage = 1;
        }

        $iPage = (int) ceil(($iCount + 1) / $iOnPage);

        return $iPage;
    }

    /**
     * Это отзыв к товару?
     *
     * @return  bool
     */
    public function isGoodReviews()
    {
        return $this->parent_class == self::GoodReviews;
    }

    /**
     * Это отзыв к разделу?
     *
     * @return  bool
     */
    public function isSectionReviews()
    {
        return $this->parent_class == '';
    }

    /**
     * Отзыв одобрен?
     *
     * @return bool
     */
    public function hasStatusApproved()
    {
        return $this->status == self::statusApproved;
    }

    /**
     * Отзыв отконён?
     *
     * @return bool
     */
    public function hasStatusRejected()
    {
        return $this->status == self::statusRejected;
    }

    /**
     * Отзыв новый(в статусе новый)?
     *
     * @return bool
     */
    public function hasStatusNew()
    {
        return $this->status == self::statusNew;
    }

    /**
     * Удалить рейтинг к отзыву.
     */
    public function removeRating()
    {
        if ($this->rating_id) {
            $sNameRatingModule = ($this->parent_class) ? CatalogViewer\Module::getNameModule() : Module::getNameModule();
            $oGoodRating = new Rating($sNameRatingModule);
            $oGoodRating->removeRatingByID($this->rating_id);
        }
    }

    public function addRating()
    {
        if ($this->isGoodReviews() and $this->hasStatusApproved() and $this->rating_id) {
            $oRating = new Rating(CatalogViewer\Module::getNameModule());
            $oRating
                ->setCheck(false)
                ->addRate($this->parent, $this->rating_id);
        }
    }

    /**
     * Удаляет отзывы вместе с удалением раздела.
     *
     * @param ModelEvent $event
     */
    public static function removeSection(ModelEvent $event)
    {
        $aReviews = self::find()
            ->where(['parent' => $event->sender->id])
            ->andWhere(['parent_class' => ''])
            ->all();

        foreach ($aReviews as $oReview) {
            $oReview->delete();
        }
    }

    /**
     * Удаляет отзывы вместе с удалением товара.
     *
     * @param int $iIdGood id удаляемого товара
     */
    public static function removeReviews4Good($iIdGood)
    {
        $aReviews = self::find()
            ->where(['parent_class' => self::GoodReviews])
            ->andWhere(['parent' => $iIdGood])
            ->all();

        foreach ($aReviews as $oReview) {
            $oReview->delete();
        }
    }

    /**
     * Возвращает максимальную дату модификации сущности.
     *
     * @return array|bool
     */
    public static function getMaxLastModifyDate()
    {
        return self::find()->max('last_modified_date');
    }
}
