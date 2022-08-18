<?php

namespace skewer\build\Tool\Gallery;

use skewer\base\ui;
use skewer\build\Tool;
use skewer\components\auth\CurrentAdmin;
use skewer\components\gallery;

/**
 * Модуль редактирования форматов для галереи.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    /**
     * Массив полей, выводимых колонками в списке форматов для профиля настроек.
     *
     * @var array
     */
    protected $aFormatsListFields = ['id', 'title', 'width', 'height', 'active'];

    /**
     * Id текущего открытого профиля.
     *
     * @var int
     */
    protected $iCurrentProfile = 0;

    /**
     * Иницализация.
     */
    protected function preExecute()
    {
        /* Восстанавливаем Id текущего открытого профиля */
        $this->iCurrentProfile = $this->getInt('currentProfile');
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
            'currentProfile' => $this->iCurrentProfile,
            '_filebrowser_section' => \skewer\build\Cms\FileBrowser\Api::getAliasByModule(self::className()),
        ]);
    }

    /**
     * Вызывается в случае отсутствия явного обработчика.
     *
     * @return int
     */
    protected function actionInit()
    {
        return $this->actionGetProfilesList();
    }

    /**
     * Обработчик состояния списка профилей.
     */
    protected function actionGetProfilesList()
    {
        $this->setPanelName(\Yii::t('gallery', 'tools_profileImageSettings'), true);
        $this->iCurrentProfile = 0;

        $aProfiles = gallery\Profile::getAll();

        // НАЧАЛО: Проверка и установка профилей по умолчанию
        $aDefaults = [];
        foreach ($aProfiles as $iId => &$paProfile) {
            $sType = $paProfile['type'];
            $iActive = $paProfile['active'];
            $iDefault = $paProfile['default'];

            // Запомнить тип профиля
            isset($aDefaults[$sType]) or $aDefaults[$sType] = 0;

            if ($iActive and ($iDefault or !$aDefaults[$sType])) {
                $aDefaults[$sType] = $iId;
            }
        }
        foreach ($aDefaults as $iProfileId) {
            if ($iProfileId and !$aProfiles[$iProfileId]['default']) {
                gallery\Profile::setDefaultProfile($iProfileId);
                $aProfiles[$iProfileId]['default'] = 1;
            }
        }
        // КОНЕЦ: Проверка и установка профилей по умолчанию

        $this->render(new Tool\Gallery\view\ProfilesList([
            'bIsSystemMode' => CurrentAdmin::isSystemMode(),
            'aProfiles' => ($aProfiles ?: []),
        ]));
    }

    // func

    /**
     * Состояние редактирования профиля.
     */
    protected function actionAddUpdProfile()
    {
        // Данные по профилю
        $aData = $this->get('data');

        // Id профиля
        if ($this->iCurrentProfile) { // Отрабатывает возврат из списка форматов к профилю
             $iProfileId = $this->iCurrentProfile;
        } else {
            $iProfileId = $this->iCurrentProfile = (is_array($aData) and isset($aData['id'])) ? (int) $aData['id'] : 0;
        }

        $this->setPanelName(\Yii::t('gallery', 'tools_addProfile'), true);

        // Получаем данные профиля или заготовку под новый профиль
        $aItem = $iProfileId ? gallery\Profile::getById($iProfileId) : gallery\Profile::getProfileBlankValues();

        /*Если не установлен цвет, поставим белый*/
        if (!$aItem['watermark_color']) {
            $aItem['watermark_color'] = '#ffffff';
        }

        $this->render(new Tool\Gallery\view\AddUpdProfile([
            'aProfileTypes' => gallery\Profile::getTypes(),
            'aItem' => $aItem,
            'iProfileId' => $iProfileId,
        ]));
    }

    // func

    /**
     * Сохраняет профиль.
     *
     * @throws \Exception
     */
    protected function actionSaveProfile()
    {
        $aData = $this->get('data');

        if (isset($aData['watermark_color'])) {
            Api::validateColor($aData['watermark_color']);
        }

        if (!count($aData)) {
            throw new \Exception('Error: Data is not sent!');
        }
        gallery\Profile::setProfile($aData, $aData['id']);

        /* вывод списка */
        $this->actionGetProfilesList();
    }

    // func

    /**
     * Удаляет выбранный профиль.
     *
     * @throws \Exception
     */
    protected function actionDelProfile()
    {
        /* Данные по профилю */
        $aData = $this->get('data');
        try {
            if (!CurrentAdmin::isSystemMode()) {
                throw new \Exception('Нет прав на выполнение действия.');
            }
            if (!isset($aData['id']) or (!$aData['id'] = (int) $aData['id'])) {
                throw new \Exception('Error: Element is not removed!');
            }
            gallery\Profile::removeProfile($aData['id']);
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }
        /*Вывод списка профилей*/
        $this->actionGetProfilesList();
    }

    // func

    /** Действие: Установка настроек из спискапрофилей (активность, по умолчанию) */
    protected function actionListChange()
    {
        if ($aData = $this->get('data')) {
            // Профилем по умолчанию может быть только один активный профиль
            $iActive = (int) $aData['active'];
            $iDefault = $iActive ? (int) $aData['default'] : 0;

            gallery\Profile::setProfile(['active' => $iActive], $aData['id']);
            $iDefault ? gallery\Profile::setDefaultProfile($aData['id']) : gallery\Profile::unsetDefaultProfile($aData['id']);

            $this->actionGetProfilesList();
        }
    }

    /**
     * Выводит список форматов для профиля.
     *
     * @throws \Exception
     */
    protected function actionFormatsList()
    {
        /* Данные по профилю */
        $aData = $this->get('data');

        $this->setPanelName(\Yii::t('gallery', 'tools_formatsImage'), true);

        if ($this->iCurrentProfile) {
            $iProfileId = $this->iCurrentProfile;
        } elseif (!isset($aData['id']) or (!$iProfileId = (int) $aData['id'])) {
            throw new \Exception('Error: Formats not received!');
        }
        $aItems = gallery\Format::getByProfile($iProfileId);

        $this->render(new Tool\Gallery\view\FormatsList([
            'aItems' => $aItems,
            'bIsSystemMode' => CurrentAdmin::isSystemMode(),
            'iProfileId' => $aData['id'],
        ]));
    }

    // func

    /**
     * Сортировка форматов.
     */
    protected function actionSortFormats()
    {
        $aData = $this->get('data');
        $aDropData = $this->get('dropData');
        $sPosition = $this->get('position');

        if (!isset($aData['id']) or !$aData['id'] or
            !isset($aDropData['id']) or !$aDropData['id'] or !$sPosition) {
            $this->addError('Ошибка! Неверно заданы параметры сортировки');
        }

        if (!gallery\Format::sortFormats($aData['id'], $aDropData['id'], $sPosition)) {
            $this->addError('Ошибка! Неверно заданы параметры сортировки');
        }
    }

    /** Вывести список выравнивания водяного знака относительно изображения
     * @return array
     */
    private static function getWatermarkCalibrateList()
    {
        return [
            gallery\Config::alignWatermarkTopLeft => \Yii::t('gallery', 'water_top_left'),
            gallery\Config::alignWatermarkTopRight => \Yii::t('gallery', 'water_top_right'),
            gallery\Config::alignWatermarkBottomLeft => \Yii::t('gallery', 'water_bottom_left'),
            gallery\Config::alignWatermarkBottomRight => \Yii::t('gallery', 'water_bottom_right'),
            gallery\Config::alignWatermarkCenter => \Yii::t('gallery', 'water_center'),
        ];
    }

    /**
     * Состояние добавления/редактирования формата.
     */
    protected function actionAddUpdFormat()
    {
        try {
            // Данные по формату
            $aData = $this->get('data');

            $iFormatId = (is_array($aData) && isset($aData['id'])) ? (int) $aData['id'] : 0;
            $iProfileId = $this->iCurrentProfile;

            $this->setPanelName(\Yii::t('gallery', 'tools_addFormat'), true);

            $this->setPanelName(\Yii::t('gallery', 'tools_addFormat'), true);
            if ($iFormatId) {
                $this->setPanelName(\Yii::t('gallery', 'tools_editFormat'), true);
            }

            // Получаем данные формата или заготовку под новый формат
            $aValues = $iFormatId ? gallery\Format::getById($iFormatId) : gallery\Format::getFormatBlankValues(['profile_id' => $iProfileId]);

            $this->render(new Tool\Gallery\view\AddUpdFormat([
                'aWatermarkCalibrateList' => self::getWatermarkCalibrateList(),
                'aValues' => $aValues,
                'bCanDelete' => ($iFormatId and CurrentAdmin::isSystemMode()),
            ]));
        } catch (\Exception $e) {
            echo $e;
        }
    }

    // func

    /**
     * Сохраняет формат в профиле настроек.
     *
     * @throws \Exception
     */
    protected function actionSaveFormat()
    {
        /* Сохранение данных формата */

        $aData = $this->get('data');

        if (!count($aData)) {
            throw new \Exception('Error: Data is not send!');
        }
        $iFormatId = ($aData['id']) ? $aData['id'] : false;

        /* Привязка к профилю обязательна */
        if (!isset($aData['profile_id']) or (!$aData['profile_id'] = (int) $aData['profile_id'])) {
            throw new \Exception('Error: Data is not send!');
        }
        // если водяной знак - файл, то проверить, что он png
        $sWatermark = $aData['watermark'] ?? '';
        if ($sWatermark) {
            $sPossibleFileName = WEBPATH . $sWatermark;
            if (file_exists($sPossibleFileName)) {
                $aFile = getimagesize($sPossibleFileName);
                if ($aFile[2] !== 3) {
                    $this->addError(\Yii::t('gallery', 'invalid_file_format_for_the_watermark'));
                }
            }
        }

        // Добавляем либо обнавляем формат
        gallery\Format::setFormat($aData, $iFormatId);

        $this->addMessage(\Yii::t('gallery', 'tools_saveFormatMessage'));

        /* вывод списка */
        $this->actionFormatsList();
    }

    // func

    /**
     * Удаляет выбранный формат
     *
     * @throws \Exception
     */
    protected function actionDelFormat()
    {
        /* Данные по формату */
        $aData = $this->get('data');

        try {
            if (!CurrentAdmin::isSystemMode()) {
                throw new \Exception('Нет прав на выполнение действия.');
            }
            if (!isset($aData['id']) or (!$aData['id'] = (int) $aData['id'])) {
                throw new \Exception('Error: Element(Format) is not removed!');
            }
            /*Удаление формат*/
            gallery\Format::removeFormat($aData['id']);
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        /*Вывод списка профилей*/
        $this->actionFormatsList();
    }

    // func
}
