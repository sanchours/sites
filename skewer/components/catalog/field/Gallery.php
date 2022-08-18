<?php

namespace skewer\components\catalog\field;

use skewer\base\SysVar;
use skewer\build\Catalog\Goods\SeoGood;
use skewer\components\filters\FilterPrototype;
use skewer\components\gallery\models\Photos;
use skewer\components\gallery\Photo;
use skewer\components\gallery\Profile;
use yii\helpers\ArrayHelper;

class Gallery extends Prototype
{
    public $disableEdit = true;

    public $isLinked = true;

    protected function build($value, $rowId, $aParams)
    {
        $aImages = Photo::getFromAlbum($value, true) ?: [];

        $html = $this->buildHtml($value, $aImages);

        return [
            'value' => $value,
            'gallery' => ['images' => &$aImages],
            'first_img' => reset($aImages),
            'html' => $html,
            'tab' => $html,
        ];
    }

    /**
     * Построит html представление поля.
     *
     * @param mixed $mAlbumId - альбом/-ы
     * @param Photos[] $aImages - изобраэения альбома
     *
     * @return string
     */
    private function buildHtml($mAlbumId, $aImages)
    {
        $sWidget = ($this->widget) ? $this->widget . '.twig' : '';

        $protectFancyBox = SysVar::get('Page.not_save_image_fancybox', 0);
        $transitionEffectFancybox = SysVar::get('Page.image_change_effect', 'disable');

        $html = '';

        if ($sWidget) {
            $html = $this->getHtmlData($mAlbumId, $sWidget, [
                'aImages' => $aImages,
                'protect' => $protectFancyBox,
                'transitionEffect' => $transitionEffectFancybox,
            ]);
        }

        return $html;
    }

    /**
     * Установка seo данных
     * Перекрываем alt_title и title изображениям фотогалерей.
     *
     * @param SeoGood $oSeo
     * @param $aField
     * @param $iSectionId
     *
     * @return mixed
     */
    public function setSeo($oSeo, &$aField, $iSectionId)
    {
        if (ArrayHelper::getValue($aField, 'gallery.images')) {
            /** @var Photos $image */
            foreach ($aField['gallery']['images'] as $image) {
                if (!$image->alt_title) {
                    $image->alt_title = $oSeo->parseField('altTitle', ['sectionId' => $iSectionId, 'label_number_photo' => $image->priority]);
                }

                if (!$image->title) {
                    $image->title = $oSeo->parseField('nameImage', ['sectionId' => $iSectionId, 'label_number_photo' => $image->priority]);
                }

                $oSeo->clearLabelsFromEntity();
            }

            $aField['html'] = $this->buildHtml($aField['value'], $aField['gallery']['images']);
        }

        return $aField;
    }

    public static function getGroupWidgetList($link_id = '')
    {
        $aProfile = Profile::getById($link_id);
        if ($aProfile) {
            switch ($aProfile['alias']) {
                case Profile::TYPE_CATALOG:
                    $aWidgetList = [
                        FilterPrototype::TYPE_GALLERY => \Yii::t('Card', 'widget_gallery'),
                        FilterPrototype::TYPE_FOTORAMA => \Yii::t('Card', 'widget_fotorama'),
                    ];
                    break;
                case Profile::TYPE_CATALOG_ADD:
                    $aWidgetList = [
                        FilterPrototype::TYPE_FOTORAMA => \Yii::t('Card', 'widget_fotorama'),
                        FilterPrototype::TYPE_TILE => \Yii::t('Card', 'widget_tile'),
                    ];
                    break;
            }
        }

        return $aWidgetList ?? [];
    }

    public static function getEntityList($link_id = '')
    {
        $aOut = Profile::getActiveByType(Profile::TYPE_CATALOG, true);
        $aOut += Profile::getActiveByType(Profile::TYPE_CATALOG_ADD, true);

        // Добавить использующийся профиль галереи в независимости от его активности
        if ($aProfileCurrent = Profile::getById($link_id)) {
            $aOut[$link_id] = $aProfileCurrent['title'];
        }

        return $aOut;
    }

    /**
     * Выбрать профиль для галереи по умолчанию.
     *
     * @param $iTypeId
     *
     * @return array
     */
    public static function getDefaultGallery(&$iTypeId)
    {
        $iTypeId = Profile::getDefaultId(Profile::TYPE_CATALOG);
        $aWidgetList = self::getGroupWidgetList($iTypeId);

        return $aWidgetList;
    }
}
