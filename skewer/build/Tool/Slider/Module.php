<?php

namespace skewer\build\Tool\Slider;

use skewer\base\orm\Query;
use skewer\base\site\Site;
use skewer\base\ui\ARSaveException;
use skewer\base\ui\state\BaseInterface;
use skewer\build\Tool;
use skewer\ext\jqueryui;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use yii\web\JqueryAsset;

/**
 * Проекция редактора баннеров для слайдера в панель управления
 * Class Module.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    protected $iCurrentBanner = 0;

    protected $iCurrentSlide = 0;

    /** @var int Параметр из раздела */
    protected $currentBanner = 0;

    protected function preExecute()
    {
        if ($this->currentBanner) {
            $this->iCurrentBanner = $this->currentBanner;
        }
    }

    /**
     * Разводящее первичное состояние.
     */
    protected function actionInit()
    {
        if (Site::isNewAdmin()) {
            $this->actionShowIframe();

            return;
        }

        // вывод списка
        if ($this->currentBanner) {
            $this->actionSlideList();
        } else {
            $this->actionBannerList();
        }

        $oBundle = \Yii::$app->getAssetManager()->getBundle(Asset::className());

        $this->addCssFile($oBundle->baseUrl . '/css/SlideShower.css');
        $this->addJsFile($oBundle->baseUrl . '/js/SlideLoadImage.js');
        $this->addJsFile($oBundle->baseUrl . '/js/SlideShower.js');
    }

    /**
     * Показывает кнопку для новой админки.
     */
    protected function actionShowIframe()
    {
        $this->render(new view\Iframe([]));
    }

    /**
     * Список баннеров.
     */
    protected function actionBannerList()
    {
        $this->iCurrentBanner = 0;

        $aItems = models\BannersMain::getAllBannersWithPreviewImage();

        $this->setInnerData('currentBanner', false);

        $this->render(new view\Index([
            'items' => $aItems,
        ]));
    }

    /**
     * Форма редактирования баннера.
     */
    protected function actionEditBannerForm()
    {
        $iBannerId = $this->getInnerData('currentBanner');

        $oBannerRow = models\BannersMain::getNewOrExist($iBannerId);

        if (!$iBannerId) {
            $oBannerRow->link_target = Tool\Slider\Api::TARGET_TYPE_BLANK;
        }

        $this->render(new view\editBannerForm([
            'oItem' => $oBannerRow,
            'iBannerId' => $iBannerId,
        ]));

        return psComplete;
    }

    /**
     * Добавление/обновление баннера.
     */
    protected function actionSaveBanner()
    {
        $aData = $this->getInData();

        $iBannerId = $this->getInDataValInt('id');

        $oRow = models\BannersMain::getNewOrExist($iBannerId);

        if (!$oRow) {
            throw new UserException("Не найдена запись {$iBannerId}");
        }

        $prevLinkTarget = $oRow->link_target;
        $oRow->setAttributes($aData);

        if (!$oRow->save()) {
            throw new ARSaveException($oRow);
        }

        if ($aData['link_target'] != $prevLinkTarget) {
            models\BannerSlides::updateAll(
                ['link_target' => $oRow->link_target],
                ['banner_id' => $oRow->id]
            );
        }
        $this->actionBannerList();
    }

    /**
     * Удаление баннера.
     */
    protected function actionDelBanner()
    {
        $iBannerId = $this->getInDataValInt('id');

        if (!$iBannerId || !($oRow = models\BannersMain::findOne(['id' => $iBannerId]))) {
            throw new UserException("not found banner id={$iBannerId}");
        }

        $oRow->delete();

        $this->actionBannerList();
    }

    /**
     * Список слайдов для баннера.
     */
    protected function actionSlideList()
    {
        $aData = $this->getInData();
        if (!$this->iCurrentBanner && !empty($aData['id'])) {
            $this->iCurrentBanner = $aData['id'];
        }

        $this->iCurrentSlide = 0;

        $this->setInnerData('currentBanner', $this->iCurrentBanner);
        if (!$this->iCurrentBanner) {
            throw new UserException(\Yii::t('slider', 'noFindBanner'));
        }
        $aItems = models\BannerSlides::getSlides4BannerWithPreview($this->iCurrentBanner);

        $this->render(new view\slideList([
            'aItems' => $aItems,
            'iCurrentBanner' => $this->currentBanner,
        ]));

        return psComplete;
    }

    protected function actionSortSlideList()
    {
        $aData = $this->get('data');
        $aDropData = $this->get('dropData');
        $sPosition = $this->get('position');

        if (empty($aData['id']) || empty($aDropData) || !$sPosition) {
            $this->addError(\Yii::t('slider', 'sortError'));
        }

        if (!models\BannerSlides::sort($aData['id'], $aDropData['id'], $sPosition)) {
            $this->addError(\Yii::t('slider', 'sortError'));
        }
    }

    /**
     * Форма редактирования слайда.
     *
     * @throws \yii\base\InvalidConfigException
     *
     * @return int
     */
    protected function actionEditSlideForm()
    {
        if (!$this->iCurrentBanner) {
            throw new \Exception(\Yii::t('slider', 'noFindBanner'));
        }

        $aData = $this->getInData();
        $iItemId = ArrayHelper::getValue($aData, 'id', $this->iCurrentSlide);
        $this->iCurrentSlide = $iItemId;

        $sUploadImage = ArrayHelper::getValue($aData, 'upload_image', '');

        $this->addJsFile(\Yii::$app->getAssetManager()->getBundle(JqueryAsset::className())->baseUrl . '/jquery.js');
        $this->addJsFile(\Yii::$app->getAssetManager()->getBundle(jqueryui\Asset::className())->baseUrl . '/jquery-ui.min.js');

        $oSlideRow = models\BannerSlides::getNewOrExist($iItemId);

        if (!$oSlideRow->link_target) {
            $oBanner = models\BannersMain::findOne(['id' => $this->iCurrentBanner]);
            if ($oBanner instanceof models\BannersMain) {
                $oSlideRow->link_target = $oBanner->link_target;
            }
        }

        $aSlideData = $oSlideRow->getAttributes();

        if ($sUploadImage) {
            $aSlideData['img'] = $sUploadImage;
        }

        if (!$aSlideData['img']) {
            $aSlideData['img'] = Api::getEmptyImgWebPath();
        }

        $this->addInitParam('lang', ['galleryUploadingImage' => \Yii::t('gallery', 'galleryUploadingImage')]);

        $this->render(new view\editSlideForm([
            'oItem' => $aSlideData,
            'iItemId' => $iItemId,
        ]));

        return psComplete;
    }

    /**
     * Форма расширенного редактирования текстов для слайда.
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function actionEditSlideText()
    {
        if (!$this->iCurrentSlide) {
            throw new \Exception(\Yii::t('slider', 'noFindSlide'));
        }
        $oSlideRow = models\BannerSlides::findOne(['id' => $this->iCurrentSlide]);

        $this->render(new view\editSlideText([
            'oItem' => $oSlideRow,
        ]));

        return psComplete;
    }

    /**
     * Обработка загруженного изображения для фона слайда.
     */
    protected function actionUploadImage()
    {
        $iItemId = (int) $this->get('slideId');

        $sSourceFN = Api::uploadFile();

        $this->set(
            'data',
            [
                'id' => $iItemId,
                'upload_image' => $sSourceFN,
            ]
        );

        $this->actionEditSlideForm();
    }

    /**
     * Состояние сохранения слайда.
     */
    protected function actionSaveSlide()
    {
        $aData = $this->getInData();

        $bBackToEdit = ArrayHelper::getValue($aData, 'back2edit', false);

        if (!$this->iCurrentBanner) {
            throw new \Exception(\Yii::t('slider', 'noFindBanner'));
        }
        $iSlideId = !empty($aData['id']) ? $aData['id'] : false;

        $oSlideRow = models\BannerSlides::getNewOrExist($iSlideId);

        //при обновлении из списка приходят неактуальные данные
        unset($aData['position']);

        $oSlideRow->setAttributes($aData);

        $oSlideRow->banner_id = $this->iCurrentBanner;

        if (!$oSlideRow->save()) {
            throw new ARSaveException($oSlideRow);
        }
        $this->set('data', false);

        if ($bBackToEdit) {
            $this->actionEditSlideForm();
        } else {
            $this->actionSlideList();
        }
    }

    /**
     * Состояние удаления слайда.
     */
    protected function actionDelSlide()
    {
        $aData = $this->getInData();

        if (!$this->iCurrentBanner) {
            throw new UserException(\Yii::t('slider', 'noFindBanner'));
        }
        $iSlideId = ArrayHelper::getValue($aData, 'id', false);

        if (!$iSlideId) {
            throw new \Exception(\Yii::t('slider', 'noFindSlide'));
        }
        $oSlideRow = models\BannerSlides::findOne(['id' => $iSlideId]);

        if (!$oSlideRow) {
            throw new \Exception(\Yii::t('slider', 'noFindSlide'));
        }
        $oSlideRow->delete();

        $this->actionSlideList();
    }

    /**
     * Форма настройки параметров показа баннеров.
     *
     * @return int
     */
    protected function actionToolsForm()
    {
        $aToolData = [];
        $aItems = Query::SelectFrom('banners_tools')->getAll();
        foreach ($aItems as $aItem) {
            $aToolData[$aItem['bt_key']] = $aItem['bt_value'];
        }

        $aHeightParams = [
            'minValue' => 0,
            'allowDecimals' => false,
        ];

        // #41095 Если задана адаптивность слайдеру, то запретить менять его высоту
        if (isset($aToolData['responsive']) and $aToolData['responsive']) {
            $aHeightParams['disabled'] = 1;
            $aToolData['maxHeight'] = '';
        }

        $this->render(new view\toolsForm([
            'aHeightParams' => $aHeightParams,
            'aToolData' => $aToolData,
        ]));

        return psComplete;
    }

    /**
     * Состояние сохранения параметров показа баннеров.
     */
    protected function actionSaveTools()
    {
        $aData = $this->get('data');

        if ($aData['transitionduration'] < 0) {
            throw new UserException(\Yii::t('slider', 'invalid_transitionduration'));
        }
        if ($aData['autoplay'] < 0) {
            throw new UserException(\Yii::t('slider', 'invalid_autoplay'));
        }
        // Максимальная высота должна быть >= минимальных высот
        if (!empty($aData['maxHeight'])) {
            foreach (['minHeight350', 'minHeight768', 'minHeight1024', 'minHeight1280'] as $item) {
                if (isset($aData[$item]) && ($aData['maxHeight'] < $aData[$item])) {
                    throw new UserException(\Yii::t('slider', 'error_maxheight_less_minheight', ['paramMax' => \Yii::t('slider', 'fotorama_maxHeight'), 'paramMin' => \Yii::t('slider', $item)]));
                }
            }
        }

        foreach ($aData as $sKey => $sValue) {
            Query::InsertInto('banners_tools')
                ->set('bt_key', $sKey)
                ->set('bt_value', $sValue)
                ->onDuplicateKeyUpdate()
                ->set('bt_value', $sValue)
                ->get();
        }

        \Yii::$app->router->updateModificationDateSite();

        $this->actionBannerList();
    }

    /**
     * Установка служебных данных.
     *
     * @param BaseInterface $oIface
     */
    protected function setServiceData(BaseInterface $oIface)
    {
        $oIface->setServiceData([
            'url' => '/oldadmin/?mode=sliderBrowser',
        ]);
    }

}
