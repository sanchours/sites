<?php

namespace skewer\build\Tool\GalleryOnPage;

use skewer\base\SysVar;
use skewer\build\Page\Main\gallery\GalleryOnSite;
use skewer\build\Tool\GalleryOnPage\view\Index;
use skewer\build\Tool\LeftList\ModulePrototype;
use skewer\components\GalleryOnPage\Api;
use skewer\components\GalleryOnPage\FilePrototype;
use yii\base\UserException;

class Module extends ModulePrototype
{
    private function getTree()
    {
        $aItems = array_flip(Api::getResourceList());

        $aData = $aData2 = $aOutItems = [];

        foreach ($aItems as $key => $item) {
            if (get_parent_class($key) !== FilePrototype::className()) {
                $aData[get_parent_class($key)][] = $key;
            }
        }

        foreach ($aData[GalleryOnSite::className()] as $key => &$item) {
            if (isset($aData[$item])) {
                $aData2[GalleryOnSite::className()][$item] = $aData[$item];
            } else {
                $aData2[GalleryOnSite::className()][$item] = [];
            }
        }

        foreach ($aData2 as $key => $item) {
            $aOutItems[$key] = $aItems[$key];
            foreach ($item as $key2 => $item2) {
                $aOutItems[$key2] = '- ' . $aItems[$key2];
                if (!empty($item2)) {
                    foreach ($item2 as $key3 => $item3) {
                        $aOutItems[$item3] = '-- ' . $aItems[$item3];
                    }
                }
            }
        }

        return $aOutItems;
    }

    private function getCarouselClasses()
    {
        $aItems = $this->getTree();

        $aOut = [];

        foreach ($aItems as $key => $item) {
            $sClassName = $key;
            $oTmp = new $sClassName();

            $aAdd = [
                'class' => $key,
                'name' => $item,
            ];

            if (isset($aItems[get_parent_class($oTmp)])) {
                $aAdd['parent_class'] = $aItems[get_parent_class($oTmp)];
            }

            $aOut[] = $aAdd;
        }

        return $aOut;
    }

    protected function actionInit()
    {
        $this->render(new Index([
            'aItems' => $this->getCarouselClasses(),
        ]));
    }

    /**
     * Отрисовка формы редактирования.
     */
    protected function actionView()
    {
        $aData = $this->getInData();

        $this->setPanelName(trim($aData['name'], '-'), true);

        if (!isset($aData['class'])) {
            throw new UserException(\Yii::t('GalleryOnPage', 'no_class'));
        }
        $sClass = $aData['class'];

        $aData = Api::getSettingsByEntity(null, false, $sClass);

        $aData['class'] = $sClass;

        $aExcludedParams = $aData['class']::excludedParams();

        $this->render(new view\View([
            'data' => $aData,
            'excludedParams' => $aExcludedParams,
        ]));
    }

    /**
     * Сохранение значений.
     */
    protected function actionSave()
    {
        $aData = $this->getInData();

        Api::validateData($aData);

        if (!isset($aData['class'])) {
            throw new UserException(\Yii::t('GalleryOnPage', 'no_class'));
        }
        $sClass = $aData['class'];
        unset($aData['class']);

        $aData['autoHeight'] = self::changeType($aData['autoHeight']);
        $aData['nav'] = self::changeType($aData['nav']);
        $aData['dots'] = self::changeType($aData['dots']);
        $aData['autoWidth'] = self::changeType($aData['autoWidth']);
        $aData['shadow'] = self::changeType($aData['shadow']);
        $aData['loop'] = self::changeType($aData['loop']);

        /*Достанем из БД контейнер с данными*/
        $aContainerData = json_decode(SysVar::get('CarouselData'), true);

        if (!$aContainerData || $aContainerData === null) {
            $aContainerData = [];
        }

        /*Подменим в контейнере данные о текущей сущности*/
        $sEntity = Api::getEntityByClassName($sClass);

        $aContainerData[$sEntity] = $aData;

        /*И сохраняем*/
        SysVar::set('CarouselData', json_encode($aContainerData));

        $this->addMessage(\Yii::t('GalleryOnPage', 'success'));

        return $this->actionInit();
    }

    /**
     * Сносит данные о сущности в БД. Теперь они будут наследоваться.
     *
     * @throws UserException
     */
    protected function actionInherit()
    {
        $aData = $this->getInData();

        if (!isset($aData['class'])) {
            throw new UserException(\Yii::t('GalleryOnPage', 'no_class'));
        }
        $sClass = $aData['class'];

        $sEntity = Api::getEntityByClassName($sClass);

        Api::cleanSettingsForEntity($sEntity);

        $this->addMessage(\Yii::t('GalleryOnPage', 'inherited'));

        return $this->actionInit();
    }

    /**
     * Функция приведения типов к bool.
     *
     * @param $mValue
     *
     * @return bool
     */
    private static function changeType($mValue)
    {
        if ($mValue === 'false') {
            return false;
        }
        if ($mValue === 'true') {
            return true;
        }

        return (bool) $mValue;
    }
}
