<?php

namespace skewer\build\Tool\Slider\models;

use skewer\build\Tool\Slider\Api;
use skewer\components\ActiveRecord\ActiveRecord;

/**
 * This is the model class for table "banners_slides".
 *
 * @property int $id
 * @property int $banner_id
 * @property string $img
 * @property string $link
 * @property string $text1
 * @property int $text1_h
 * @property int $text1_v
 * @property string $text2
 * @property int $text2_h
 * @property int $text2_v
 * @property string $text3
 * @property int $text3_h
 * @property int $text3_v
 * @property string $text4
 * @property int $text4_h
 * @property int $text4_v
 * @property int $position
 * @property int $active
 * @property string $slide_link
 * @property string $link_target
 */
class BannerSlides extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'banners_slides';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['banner_id', 'position'], 'required'],
            [['banner_id', 'text1_h', 'text1_v', 'text2_h', 'text2_v', 'text3_h', 'text3_v', 'text4_h', 'text4_v', 'position', 'active'], 'integer'],
            [['text1', 'text2', 'text3', 'text4'], 'string'],
            [['img', 'link', 'slide_link', 'link_target'], 'string', 'max' => 255],
            ['text1_v', 'default', 'value' => 20],
            ['text1_h', 'default', 'value' => 30],
            ['text2_v', 'default', 'value' => 80],
            ['text2_h', 'default', 'value' => 30],
            ['text3_v', 'default', 'value' => 140],
            ['text3_h', 'default', 'value' => 70],
            ['text4_v', 'default', 'value' => 200],
            ['text4_h', 'default', 'value' => 70],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'banner_id' => 'Banner ID',
            'img' => 'Img',
            'link' => 'Link',
            'text1' => 'Text1',
            'text1_h' => 'Text1 H',
            'text1_v' => 'Text1 V',
            'text2' => 'Text2',
            'text2_h' => 'Text2 H',
            'text2_v' => 'Text2 V',
            'text3' => 'Text3',
            'text3_h' => 'Text3 H',
            'text3_v' => 'Text3 V',
            'text4' => 'Text4',
            'text4_h' => 'Text4 H',
            'text4_v' => 'Text4 V',
            'position' => 'Position',
            'active' => 'Active',
            'slide_link' => 'Slide Link',
            'link_target' => 'Link Target',
        ];
    }

    public function initSave()
    {
        $this->active = (int) $this->active;

        // не сохраняем картинку, если это загрушка
        if ($this->img == Api::getEmptyImgWebPath()) {
            $this->img = '';
        }

        if (!$this->position) {
            $this->position = self::getMaxPos4Banner($this->banner_id) + 1;
        }

        return parent::initSave();
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($changedAttributes) {
            BannersMain::updateAll(
                ['last_modified_date' => date('Y-m-d H:i:s', time())],
                ['id' => $this->banner_id]
            );
        }
    }

    public function afterDelete()
    {
        BannersMain::updateAll(
            ['last_modified_date' => date('Y-m-d H:i:s', time())],
            ['id' => $this->banner_id]
        );

        parent::afterDelete();
    }

    /**
     * Вернёт новую запись AR.
     *
     * @param $aData - данные для установки
     *
     * @return self
     */
    public static function getNewRow($aData = [])
    {
        $oRow = new self();
        $oRow->active = 1;

        if ($aData) {
            $oRow->setAttributes($aData);
        }

        return $oRow;
    }

    /**
     * Вернёт новую или существующую запись.
     *
     * @param bool|int $iSlideId - id слайда
     *
     * @return self | bool
     */
    public static function getNewOrExist($iSlideId = false)
    {
        if ($iSlideId) {
            $oRow = self::findOne(['id' => $iSlideId]);

            return $oRow ? $oRow : false;
        }

        return self::getNewRow();
    }

    /**
     * Получение набора слайдов для баннера.
     *
     * @param $iBannerId - id баннера
     * @param bool $bOnlyActive - выбирать только активные слайды
     * @param bool $bOnlyWithImg - выбирать только с изображением
     *
     * @return array
     */
    public static function getSlides4Banner($iBannerId, $bOnlyActive = true, $bOnlyWithImg = true)
    {
        $oQuery = self::find()
            ->where(['banner_id' => $iBannerId]);

        if ($bOnlyActive) {
            $oQuery->andWhere(['active' => 1]);
        }

        if ($bOnlyWithImg) {
            $oQuery->andWhere(['!=', 'img', '']);
        }

        $aSlides = $oQuery
            ->orderBy(['position' => SORT_ASC])
            ->asArray()
            ->all();

        return $aSlides;
    }

    /**
     * Вернет слайды баннера с превью изображением
     *
     * @param int $iBannerId - ид баннера
     *
     * @return array
     */
    public static function getSlides4BannerWithPreview($iBannerId)
    {
        $aSlides = self::getSlides4Banner($iBannerId, false, false);

        foreach ($aSlides as &$aSlide) {
            if (!$aSlide['img']) {
                $aSlide['preview_img'] = Api::getEmptyImgWebPath();
            } else {
                $aSlide['preview_img'] = $aSlide['img'];
            }
        }

        return $aSlides;
    }

    /**
     * Получение максимальной позиции.
     *
     * @param $iBannerId
     *
     * @return int
     */
    public static function getMaxPos4Banner($iBannerId)
    {
        $iMax = BannerSlides::find()
            ->where(['banner_id' => $iBannerId])
            ->max('position');

        return $iMax ? $iMax : 0;
    }

    public static function sort($aItemId, $aTargetId, $sOrderType = 'before')
    {
        $oItem = self::findOne(['id' => $aItemId]);
        $oTarget = self::findOne(['id' => $aTargetId]);

        if (!$oItem || !$oTarget) {
            return false;
        }

        $sSortField = 'position';

        // должны быть в одной форме
        if ($oItem->banner_id != $oTarget->banner_id) {
            return false;
        }

        $iItemPos = $oItem->{$sSortField};
        $iTargetPos = $oTarget->{$sSortField};

        // выбираем напрвление сдвига
        if ($iItemPos > $iTargetPos) {
            $iStartPos = $iTargetPos;
            if ($sOrderType == 'before') {
                --$iStartPos;
            }
            $iEndPos = $iItemPos;
            $iNewPos = $sOrderType == 'before' ? $iTargetPos : $iTargetPos + 1;
            self::shiftPosition($oItem->banner_id, $iStartPos, $iEndPos, '+');
            self::changePosition($oItem->id, $iNewPos);
        } else {
            $iStartPos = $iItemPos;
            $iEndPos = $iTargetPos;
            if ($sOrderType == 'after') {
                ++$iEndPos;
            }
            $iNewPos = $sOrderType == 'after' ? $iTargetPos : $iTargetPos - 1;
            self::shiftPosition($oItem->banner_id, $iStartPos, $iEndPos, '-');
            self::changePosition($oItem->id, $iNewPos);
        }

        \Yii::$app->router->updateModificationDateSite();

        return true;
    }

    private static function shiftPosition($iFormId, $iStartPos, $iEndPos, $sSign = '+')
    {
        self::updateAllCounters(
            [
            'position' => ($sSign == '-') ? -1 : 1, ],
            'banner_id = :banner_id AND position > :startPosition AND position < :endPosition',
            [
                'banner_id' => (int) $iFormId,
                'startPosition' => (int) $iStartPos,
                'endPosition' => (int) $iEndPos,
            ]
        );
    }

    private static function changePosition($iParamId, $iPos)
    {
        self::updateAll(['position' => (int) $iPos], ['id' => (int) $iParamId]);
    }
}
