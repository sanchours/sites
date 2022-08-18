<?php

namespace skewer\components\design;

use skewer\base\section\Parameters;
use skewer\build\Adm\CategoryViewer\models\CategoryViewerCssParams;
use skewer\components\gallery\Format;
use skewer\components\gallery\Profile;

/**
 * Прототип для переключателя подвала сайта.
 */
abstract class TplSwitchCategoryViewer extends TplSwitchPrototype
{
    /**
     * Отдает тип переключателя шаблонов.
     *
     * @return string
     */
    protected function getType()
    {
        return 'categoryViewer';
    }

    final protected function getModulesList()
    {
        return '';
    }

    final public function setModules()
    {
    }

    /**
     * Задать набор настроек для модулей.
     */
    public function setModuleSettings()
    {
        $iProfileId = Profile::getDefaultId(Profile::TYPE_CATEGORYVIEWER);

        $aFormat = Format::getByName('preview', $iProfileId);
        $iFormatId = $aFormat ? $aFormat[0]['id'] : 0;

        $aSettingFormat = [
                'profile_id' => $iProfileId,
                'title' => 'Миниатюра',
                'name' => 'preview',
            ] + $this->getSettingsFormat();

        Format::setFormat($aSettingFormat, $iFormatId);
        CategoryViewerCssParams::deleteAll();
    }

    /**
     * Задать настройки для типовых блоков.
     */
    public function setBlocks()
    {
    }

    /**
     * Установить типовой контент
     */

    /**
     * Установить типовой контент
     * Выполняется только если при запуске переключения был задан соответствующий флаг.
     */
    public function setContent()
    {
    }

    public function setTpl()
    {
        // имя старого шаблона
        $sOldTpl = $this->getOldTpl();

        // в бэкап добавляем имя старого шаблона
        $this->oBackup->setUserData('old_tpl', $sOldTpl);

        // стереть все включения определния шаблона
        Parameters::removeByName(
            'category_widget',
            'CategoryViewer'
        );

        Parameters::setParams(
            \Yii::$app->sections->tplNew(),
            'CategoryViewer',
            'category_widget',
            $this->getName()
        );
    }

    /** Получить настройки формата профиля галереи */
    public function getSettingsFormat()
    {
        return [
            'width' => 220,
            'height' => 220,
            'resize_on_larger_side' => 0,
            'scale_and_crop' => 1,
            'use_watermark' => 0,
            'watermark' => '',
            'watermark_align' => 84,
            'active' => 1,
        ];
    }

    public function getOldTpl()
    {
        return Parameters::getValByName(\Yii::$app->sections->tplNew(), 'CategoryViewer', 'category_widget');
    }

    /**
     * {@inheritdoc}
     */
    public function analyzeCssParams()
    {
        $sPath = RELEASEPATH . 'build/Page/CategoryViewer/templates/' . $this->getName() . '/tpl_common.css';
        if (file_exists($sPath)) {
            DesignManager::analyzeOneCssFiles($sPath);
        }
    }
}
