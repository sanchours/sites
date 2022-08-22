<?php

namespace skewer\build\Adm\Documents\models;

use skewer\base\ft\exception\Query as ExceptionQuery;
use skewer\base\orm\Query;
use skewer\base\router\Router;
use skewer\base\section\Parameters;
use skewer\build\Page\CatalogViewer;
use skewer\build\Page\Documents\Module;
use skewer\components\ActiveRecord\ActiveRecord;
use skewer\components\catalog\GoodsRow;
use skewer\components\catalog\model\GoodsTable;
use skewer\components\gallery\Photo;
use skewer\components\rating\Rating;
use yii\base\ModelEvent;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "documents".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $date_time
 * @property string $file
 */
class Documents extends ActiveRecord
{
    /**
     * Базовый набор виртуальных полей для экспорта на клиентскую часть.
     *
     * @return array
     */
    public function fields()
    {
        return ArrayHelper::merge(parent::fields(), [
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
        return 'documents';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_time'], 'safe'],
            [['name','description','file'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'date_time' => 'Date Time',
            'file' => 'File',
        ];
    }

    /**
     * Получить объект новой строки.
     *
     * @param array $aData - данные дял установки
     *
     * @return Documents
     */
    public static function getNewRow($aData = [])
    {
        $oRow = new self();

        $oRow->date_time = date('Y-m-d H:i:s', time());
        $oRow->name = '';
        $oRow->description = '';
        $oRow->file = '';

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

//        $this->photo_gallery = (int) $this->photo_gallery;

        if (!$this->date_time || ($this->date_time == 'null')) {
            $this->date_time = date('Y-m-d H:i:s', time());
        }

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
                $sLink = sprintf('[%d]', $this->id);
            } else {
                $sLink = sprintf('[%d][GuestBook?page=%d]', $this->id, $iNumberPage);
            }
        } else {
            // Основной раздел товара
            $aGoodData = GoodsTable::get($this->id);
            $iBaseSection = (int) ArrayHelper::getValue($aGoodData, 'section');

            // alias товара
            $oGoodRow = GoodsRow::get($this->id);
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
//    public function getImagesDataGallery()
//    {
//        $sImagesData = false;
//
//        if ($this->photo_gallery) {
//            $photos = Photo::getFromAlbum($this->photo_gallery, true);
//            $oFirstImage = $photos[0] ?? false;
//            if ($oFirstImage) {
//                $sImagesData = $oFirstImage->images_data;
//            }
//        }
//
//        return $sImagesData;
//    }

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
        $iCount = Documents::find()
            ->andWhere(['>', 'date_time', $this->date_time])
            ->count(['id' => $this->id]);

        if ($this->isSectionReviews()) {
            $iOnPage = Parameters::getValByName($this->id, 'content', 'onPageContent', true);

            if (!$iOnPage) {
                $iOnPage = Parameters::getValByName($this->id, 'content', 'onPage', true);
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

}
