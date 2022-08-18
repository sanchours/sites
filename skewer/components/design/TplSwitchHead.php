<?php

namespace skewer\components\design;

use skewer\base\orm\Query;
use skewer\base\section\Page;
use skewer\build\Tool\Slider\models\BannerSlides;
use skewer\build\Tool\Slider\models\BannersMain;
use yii\helpers\ArrayHelper;

/**
 * Прототип для переключателя шапки сайта.
 */
abstract class TplSwitchHead extends TplSwitchPrototype
{
    public $sPathDir = '';

    /**
     * Отдает тип переключателя шаблонов.
     *
     * @return string
     */
    protected function getType()
    {
        return 'head';
    }

    /**
     * {@inheritdoc}
     */
    protected function setBlockText($sName, $sText)
    {
        if (mb_strpos($sName, 'page.head.pilot') === 0) {
            $sName = 'headtext' . mb_substr($sName, -1);
        }

        parent::setBlockText($sName, $sText);
    }

    /**
     * Задает логотип сайта.
     *
     * @param string $sPath путь до изображения
     *
     * @return false|string
     */
    public function setLogo($sPath)
    {
        return $this->setParam(
            \Yii::$app->sections->getValue(Page::LANG_ROOT),
            '.',
            'site_nlogo',
            $sPath
        );
    }

    /**
     * Добавляет слайдер для текущей темы.
     *
     * @param string $sImage путь до изображения для слайдера
     * @param array $aBannerData доп даные для банера
     *      on_include
     *      bullet = dots / thumbs / false
     *      scroll = always / true / false
     * @param array $aSlideData доп даные для отдельного слайда
     *      textN = text
     *      textN_h = int
     *      textN_v = int
     *  где N - 1 / 2 / 3 / 4
     */
    protected function setSlider($sImage, $aBannerData = [], $aSlideData = [])
    {
        // сформировать имя
        $sName = 'example_' . $this->getName();

        // деактивировать все
        BannersMain::updateAll(['active' => 0]);

        // найти слайдер
        $oBanner = BannersMain::findOne(['title' => $sName]);

        // если нет - создать
        if (!$oBanner) {
            $oBanner = BannersMain::getNewRow(ArrayHelper::merge([
                'title' => $sName,
                'section' => \Yii::$app->sections->main(),
                'active' => 1,
            ], $aBannerData));

            $oBanner->save();

            $sType = mb_substr($sImage, mb_strripos($sImage, '.') + 1);

            $sSlideImg = sprintf(
                '/files/design/%s.slide.%s.%s',
                $this->getName(),
                time(),
                $sType
            );

            copy($sImage, WEBPATH . $sSlideImg);

            // первый
            $oSlide = BannerSlides::getNewRow(ArrayHelper::merge([
                'active' => 1,
                'img' => $sSlideImg,
                'banner_id' => $oBanner->id,
            ], $aSlideData));

            $oSlide->save();

            // второй
            $oSlide = BannerSlides::getNewRow(ArrayHelper::merge([
                'active' => 1,
                'img' => $sSlideImg,
                'banner_id' => $oBanner->id,
            ], $aSlideData));

            $oSlide->save();

            // третий
            $oSlide = BannerSlides::getNewRow(ArrayHelper::merge([
                'active' => 1,
                'img' => $sSlideImg,
                'banner_id' => $oBanner->id,
            ], $aSlideData));

            $oSlide->save();
        }

        // активировать наш
        $oBanner->active = 1;
        $oBanner->save();
    }

    /**
     * Задает настройки для слайдера.
     *
     * @param [] $aData
     *      transition = slide / crossfade / dissolve
     *      autoplay = 1 / 0
     *      loop = 1 / 0
     *      transitionduration = int
     *      maxHeight = int
     */
    protected function setSliderTools($aData)
    {
        foreach ($aData as $sKey => $sValue) {
            Query::InsertInto('banners_tools')
                ->set('bt_key', $sKey)
                ->set('bt_value', $sValue)
                ->onDuplicateKeyUpdate()
                ->set('bt_value', $sValue)
                ->get();
        }
    }

    public function analyzeCssParams()
    {
        $sClassName = '\skewer\build\Page\Main\templates\head\\' . $this->getName() . '\Asset';
        if (class_exists($sClassName)) {
            DesignManager::analyzeOneAsset($sClassName);
        }
    }
}
