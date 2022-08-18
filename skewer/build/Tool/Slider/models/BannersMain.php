<?php

namespace skewer\build\Tool\Slider\models;

use skewer\base\section\Tree;
use skewer\build\Tool\Slider\Api;
use skewer\components\ActiveRecord\ActiveRecord;

/**
 * This is the model class for table "banners_main".
 *
 * @property int $id
 * @property string $title
 * @property int $section
 * @property int $on_include
 * @property string $bullet
 * @property string $scroll
 * @property int $active
 * @property string $last_modified_date
 * @property string $link_target
 */
class BannersMain extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'banners_main';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
//            [['title', 'section', 'on_include', 'bullet', 'scroll', 'active'], 'required'],
            [['section', 'on_include', 'active'], 'integer'],
            [['last_modified_date'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['bullet', 'scroll', 'link_target'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'section' => 'Section',
            'on_include' => 'On Include',
            'bullet' => 'Bullet',
            'scroll' => 'Scroll',
            'active' => 'Active',
            'last_modified_date' => 'Last Modified Date',
            'link_target' => 'Link Target',
        ];
    }

    public function initSave()
    {
        $this->active = (int) $this->active;
        $this->last_modified_date = date('Y-m-d H:i:s', time());

        return parent::initSave();
    }

    public function afterDelete()
    {
        // удаление слайдов баннера
        BannerSlides::deleteAll(['banner_id' => $this->id]);

        \Yii::$app->router->updateModificationDateSite();

        parent::afterDelete();
    }

    /**
     * Получить новую строку.
     *
     * @param array $aData
     *
     * @return BannersMain
     */
    public static function getNewRow($aData = [])
    {
        $oRow = new self();
        $oRow->title = \Yii::t('slider', 'new_banner');
        $oRow->active = 1;
        $oRow->bullet = 'false';
        $oRow->scroll = 'always';

        if ($aData) {
            $oRow->setAttributes($aData);
        }

        return $oRow;
    }

    /**
     * Вернёт новую или существующую запись.
     *
     * @param bool|int $iBannerId - id баннера
     *
     * @return self | bool
     */
    public static function getNewOrExist($iBannerId = false)
    {
        if ($iBannerId) {
            $oRow = self::findOne(['id' => $iBannerId]);

            return $oRow ? $oRow : false;
        }

        return self::getNewRow();
    }

    /**
     * Первый активный баннер раздела.
     *
     * @param int $iSectionId - id раздела
     *
     * @return array | bool
     */
    public static function getFirstActiveBanner4Section($iSectionId)
    {
        $aTreeSection = Tree::getSectionParents($iSectionId);

        $tempBanners = self::find()
            ->where(['active' => 1, 'section' => $iSectionId])
            ->orWhere(['on_include' => 1, 'section' => $aTreeSection, 'active' => 1])
            ->asArray()
            ->all();

        // ищем баннеры заданные для раздела
        $bUseParentBanners = true;

        $aBanners = [];
        foreach ($tempBanners as $tBanner) {
            if ($tBanner['section'] == $iSectionId) {
                $bUseParentBanners = false;
            }
            //проверим есть ли слайды в этом банере
            if (self::getCountActiveSlides4Banner($tBanner['id']) > 0) {
                $aBanners[] = $tBanner;
            }
        }

        // если нашли - удаляем баннеры родителей
        if (!$bUseParentBanners) {
            foreach ($aBanners as $iKey => $aBanner) {
                if ($aBanner['section'] != $iSectionId) {
                    unset($aBanners[$iKey]);
                }
            }

            $aBanners = array_values($aBanners);
        }

        $aFirstBanner = reset($aBanners);

        return $aFirstBanner;
    }

    /**
     * Получаем поличество слайдов в банере.
     *
     * @param int $iBannerId
     *
     * @return int
     */
    public static function getCountActiveSlides4Banner($iBannerId)
    {
        return BannerSlides::find()
            ->where(['banner_id' => $iBannerId])
            ->andWhere(['active' => 1])
            ->count();
    }

    /**
     * Вернёт первый активный слайд баннера.
     *
     * @return array|\yii\db\ActiveRecord
     */
    public function getFirstActiveSlide()
    {
        return $this->hasOne(BannerSlides::className(), ['banner_id' => 'id'])
            ->where([BannerSlides::tableName() . '.active' => 1])
            ->orderBy([BannerSlides::tableName() . '.position' => SORT_ASC])
            ->one();
    }

    /**
     * Вернёт список всех баннеров с превью изображениями.
     *
     * @return array
     */
    public static function getAllBannersWithPreviewImage()
    {
        $aBannerList = [];

        /** @var self $banner */
        foreach (self::find()->each() as $banner) {
            $aCurBanner = $banner->getAttributes();

            /** @var BannerSlides $oFirstSlide */
            $oFirstSlide = $banner->getFirstActiveSlide();

            if ($oFirstSlide && $oFirstSlide->img) {
                $aCurBanner['preview_img'] = $oFirstSlide->img;
            } else {
                $aCurBanner['preview_img'] = Api::getEmptyImgWebPath();
            }

            $aBannerList[] = $aCurBanner;
        }

        return $aBannerList;
    }
}
