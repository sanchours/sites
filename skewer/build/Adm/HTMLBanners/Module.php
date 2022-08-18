<?php

namespace skewer\build\Adm\HTMLBanners;

use skewer\base\ui;
use skewer\build\Adm;
use skewer\build\Adm\HTMLBanners\models\Banners as Banners;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Class Module.
 */
class Module extends Adm\Tree\ModulePrototype
{
    public $iPage = 0;
    public $iOnPage = 50;

    protected function preExecute()
    {
        // номер страницы
        $this->iPage = $this->getInt('page');
    }

    protected function actionInit()
    {
        $this->actionList();
    }

    /**
     * Список баннеров.
     */
    protected function actionList()
    {
        $sort = $this->getInnerData('sort', 'id');
        if ($sort !== 'id') {
            $banners = Banners::find()
                ->orderBy([$sort => SORT_DESC])
                ->asArray()
                ->all();
        } else {
            $banners = Banners::find()
                ->asArray()
                ->all();
        }

        if ($sort == 'location') {
            usort($banners, static function ($banner1, $banner2) {
                return Api::getBannerLocationsPos()[$banner1['location']] > Api::getBannerLocationsPos()[$banner2['location']];
            });
        }

        $this->render(new view\Index([
            'items' => $banners,
        ]));
    }

    /*
     * Драг энд дроп
     * */
    protected function actionsortItems()
    {
    }

    /*
     * Сохранение из списка
     * */
    protected function actionSaveFromList()
    {
        $iId = $this->getInDataValInt('id');

        $sFieldName = $this->get('field_name');

        $oRow = Banners::findOne(['id' => $iId]);
        /** @var Banners $oRow */
        if (!$oRow) {
            throw new UserException("Запись [{$iId}] не найдена");
        }
        $oRow->{$sFieldName} = $this->getInDataVal($sFieldName);

        $oRow->save();

        $this->actionInit();
    }

    /**
     * Отображение формы.
     */
    protected function actionShow()
    {
        $aData = $this->get('data');
        $iItemId = ArrayHelper::getValue($aData, 'id', 0);
        /** @var Banners $oBannersRow */
        $oBannersRow = Banners::findOne(['id' => $iItemId]);
        $this->showForm($oBannersRow);
    }

    protected function actionSort()
    {
        $this->setInnerData('sort', 'location');
        $this->actionList();
    }

    /**
     * Форма добавления.
     */
    protected function actionNew()
    {
        $this->showForm(Banners::getBlankBanner());
    }

    /**
     * Отображение формы добавления/редактирования Баннера.
     *
     * @param Banners $oItem
     *
     * @throws UserException
     */
    private function showForm(Banners $oItem)
    {
        if (!$oItem) {
            throw new UserException('Item not found');
        }
        $this->render(new view\Form([
            'item' => $oItem->getAttributes(),
        ]));
    }

    /**
     * Сохранение баннера.
     */
    protected function actionSave()
    {
        $aData = $this->get('data');

        $iId = $this->getInDataValInt('id');

        if (!$aData) {
            throw new UserException('Empty data');
        }
        if ($iId) {
            $oBannersRow = Banners::findOne(['id' => $iId]);
            if (!$oBannersRow) {
                throw new UserException("Запись [{$iId}] не найдена");
            }
        } else {
            $oBannersRow = Banners::getNewRow();
        }
        $oBannersRow->setAttributes($aData);

        $oBannersRow->save();

        $this->addModuleNoticeReport(\Yii::t('HTMLBanners', 'addBanner'), $aData);

        // вывод списка
        $this->actionInit();
    }

    /**
     * Удаляет запись.
     */
    protected function actionDelete()
    {
        // запросить данные
        $aData = $this->get('data');

        $oBanner = Banners::findOne($aData['id']);
        $oBanner->delete();

        $this->addModuleNoticeReport(\Yii::t('HTMLBanners', 'deleteBanner'), $aData);

        // вывод списка
        $this->actionList();
    }

    /**
     * Установка служебных данных.
     *
     * @param ui\state\BaseInterface $oIface
     */
    protected function setServiceData(ui\state\BaseInterface $oIface)
    {
        // установить данные для передачи интерфейсу
        $oIface->setServiceData([
            'page' => $this->iPage,
            // Параметр Идентификатора папки загрузки файлов модуля
            '_filebrowser_section' => 'Adm_HTMLBanners',
        ]);
    }
}// class
