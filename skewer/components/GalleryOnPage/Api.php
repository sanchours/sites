<?php

namespace skewer\components\GalleryOnPage;

use skewer\base\SysVar;
use yii\base\Exception;

class Api
{
    /** событие по сбору активных поисковых движков */
    const EVENT_GET_GALLERY = 'event_get_gallery';

    /** @var null|Prototype[] список поисковых движков */
    private static $aList = null;

    /**
     * Список настраиваемых параметров.
     *
     * @var array
     */
    private static $aParams = [
        'items',
        'margin',
        'dots',
        'nav',
        'autoWidth',
        'responsive',
        'slideBy',
        'loop',
        'shadow',
    ];

    /**
     * Зарегистрирует указанные классы в событийной модели как классы галлерей.
     *
     * @param $aEvents
     * @param $aClasses
     */
    public static function registerGallery(&$aEvents, $aClasses)
    {
        foreach ($aClasses as $sClass) {
            $aEvents[] = [
                'event' => \skewer\components\GalleryOnPage\Api::EVENT_GET_GALLERY,
                'class' => $sClass,
                'method' => 'getGallery',
            ];
        }
    }

    /**
     * Валидирует набор пришедших данных.
     *
     * @param $aData
     *
     * @throws Exception
     */
    public static function validateData($aData)
    {
        foreach (self::$aParams as $param) {
            if ($param === 'slideBy') {
                continue;
            }
            if (!isset($aData[$param])) {
                throw new Exception('Not set param!');
            }
        }

        if ($aData['items'] < 1) {
            throw new Exception(\Yii::t('GalleryOnPage', 'items_fail'));
        }
        if ($aData['margin'] < 0) {
            throw new Exception(\Yii::t('GalleryOnPage', 'margin_fail'));
        }
        /* Если НЕТ модуля режима адаптивности, параметр не используется */
        $oInstaller = new \skewer\components\config\installer\Api();
        if ($oInstaller->isInstalled('AdaptiveMode', \skewer\base\site\Layer::PAGE)) {
            if (json_decode($aData['responsive']) === null) {
                throw new Exception(\Yii::t('GalleryOnPage', 'adaptive_fail'));
            }
        }
    }

    /**
     *  Отдает настройки по имени режима.
     *
     * @param $sEntity
     * @param bool $bInJSON
     * @param bool $sClassName
     * @param bool $bFrontendMode
     *
     * @return mixed|string
     */
    public static function getSettingsByEntity($sEntity, $bInJSON = false, $sClassName = false, $bFrontendMode = true)
    {
        if (!$sClassName) {
            $sClassName = self::getClassNameByEntity($sEntity);
        }

        /** @var \skewer\components\GalleryOnPage\Prototype $oGallery */
        $oGallery = new $sClassName();

        $aOutData = $oGallery->getSettings();

        /*Приведение по типам*/
        $aOutData['navText'] = false;
        $aOutData['margin'] = (int) $aOutData['margin'];
        $aOutData['items'] = (int) $aOutData['items'];

        /*На фронтенде вывод параметра responsive зависит от модуля адаптивности. на бекенде нет*/
        if ($bFrontendMode) {
            /*Если НЕ работает модуль режима адаптивности, скроем параметр адаптивности*/
            $oInstaller = new \skewer\components\config\installer\Api();
            if (!$oInstaller->isInstalled('AdaptiveMode', \skewer\base\site\Layer::PAGE)) {
                unset($aOutData['responsive']);
            }
        }

        if ($bInJSON) {
            if (isset($aOutData['responsive'])) {
                $aOutData['responsive'] = json_decode($aOutData['responsive']);
            }

            return json_encode($aOutData);
        }

        return $aOutData;
    }

    public static function getEntityByClassName($sClassName)
    {
        return str_replace('GalleryOn', '', mb_stristr($sClassName, 'GalleryOn'));
    }

    public static function getClassNameByEntity($sEntity)
    {
        $aClasses = self::getResourceList();

        foreach ($aClasses as $key => $item) {
            // Последняя часть namespace
            $sTmp = basename(str_replace('\\', '/', $item));

            if (mb_strpos($sTmp, $sEntity) !== false) {
                return $item;
            }
        }
    }

    public static function cleanSettingsForEntity($sEntity)
    {
        /*Достанем из БД контейнер с данными*/
        $aContainerData = json_decode(SysVar::get('CarouselData'), true);

        if (!$aContainerData || $aContainerData === null) {
            return true;
        }

        /*Сносим в контейнере данные о текущей сущности*/
        unset($aContainerData[$sEntity]);

        /*И сохраняем*/
        SysVar::set('CarouselData', json_encode($aContainerData));

        return true;
    }

    /**
     * Отдаст список классов-галлерей которые можно использовать.
     *
     * @return null|Prototype[]|\string[]
     */
    public static function getResourceList()
    {
        if (self::$aList === null) {
            $oEvent = new GetGalleryEvent();
            \Yii::$app->trigger(self::EVENT_GET_GALLERY, $oEvent);
            self::$aList = $oEvent->getList();
        }

        return self::$aList;
    }
}
