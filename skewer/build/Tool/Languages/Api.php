<?php

namespace skewer\build\Tool\Languages;

use skewer\base\command\Hub;
use skewer\base\section\Page;
use skewer\base\site\Layer;
use skewer\base\site\Type;
use skewer\components\config\installer;
use skewer\components\i18n\command\add_branch;
use skewer\components\i18n\command\delete_branch;
use skewer\components\i18n\command\switch_language;
use skewer\components\i18n\models;

class Api
{
    public static function addBranch($sLanguage, $copy, $bCopySection)
    {
        /** @var models\Language $oLanguage */
        $oLanguage = models\Language::findOne(['name' => $sLanguage]);

        if (!$oLanguage) {
            throw new \Exception(\Yii::t('languages', 'error_lang_not_found'));
        }
        if ($oLanguage->active) {
            throw new \Exception(\Yii::t('languages', 'error_lang_is_used'));
        }
        /** @var models\Language $oSource */
        $oSource = models\Language::findOne(['name' => $copy]);

        if (!$oSource) {
            throw new \Exception(\Yii::t('languages', 'error_src_language_not_found'));
        }
        $oHub = new Hub();

        $langRootSrc = \Yii::$app->sections->getValue(Page::LANG_ROOT, $oSource->name);

        if (!$langRootSrc || $langRootSrc == \Yii::$app->sections->root()) {
            // Блок создания первой языковой ветки

            /** Чистый сайт - добавим языковую ветку на существующие разделы */
            $oCommand = new add_branch\CreateRootSection($oSource, $oSource);
            $oCommand->allInSection = true;
            $oCommand->noDelete = true;
            $oHub->addCommand($oCommand);

            /* Копирование индивидуальных для языковых веток параметры из метки "." */
            $oHub->addCommand(new add_branch\LanguageParams($oSource, null));

            /* Перенос параметров модуля настройки параметров из корневого раздела */
            $oHub->addCommand((new add_branch\ParamSettings($oSource, null))->movingFromRoot());
        }

        /* Создание главного  раздела языковой ветки */
        $oHub->addCommand(new add_branch\CreateRootSection($oLanguage, $oSource));
        /** Копирование ветки разделов */
        $oCommandCopy = new add_branch\CopySections($oLanguage, $oSource);
        if ($bCopySection) {
            $oCommandCopy->bAllCopy = true;
        }
        $oHub->addCommand($oCommandCopy);
        /* Копирование индивидуальных для языковых веток параметры из метки "." */
        $oHub->addCommand(new add_branch\LanguageParams($oLanguage, $oSource));
        /* Ссылки разделов */
        $oHub->addCommand(new add_branch\LinkSection($oLanguage, $oSource));
        /* Разводка категорий */
        $oHub->addCommand(new add_branch\CategoryViewer($oLanguage, $oSource));
        /* Редиректы на главных страницах */
        $oHub->addCommand(new add_branch\RedirectMain($oLanguage, $oSource));
        /* Языковые параметры модулей */
        $oHub->addCommand(new add_branch\ModuleParams($oLanguage, $oSource));
        /* Копирование параметров модуля настройки параметров */
        $oHub->addCommand(new add_branch\ParamSettings($oLanguage, $oSource));

        /* Каталожный сайт */
        if (Type::hasCatalogModule()) {
            /* Скопируем форму заказа! */
            $oHub->addCommand(new add_branch\OrderForm($oLanguage, $oSource));
        }

        if (Type::isShop()) {
            /* Переведем статусы заказа! */
            $oHub->addCommand(new add_branch\OrderStatus($oLanguage, $oSource));
        }

        /* Системные разделы */
        $oHub->addCommand(new add_branch\ServiceSection($oLanguage, $oSource));
        /* Сброс поиска */
        $oHub->addCommand(new add_branch\SearchUpdate($oLanguage, $oSource));
        /*
         * это должна быть последняя команда!
         */
        /* Активация языка */
        $oHub->addCommand(new add_branch\ActivateLanguage($oLanguage, null));

        $oHub->executeOrExcept();
    }

    /**
     * Удаление языковой ветки.
     *
     * @param string $sLanguage
     *
     * @throws \Exception
     */
    public static function deleteBranch($sLanguage)
    {
        /** @var models\Language $oLanguage */
        $oLanguage = models\Language::findOne(['name' => $sLanguage]);

        if (!$oLanguage) {
            throw new \Exception(\Yii::t('languages', 'error_lang_not_found'));
        }
        if (!$oLanguage->active) {
            throw new \Exception(\Yii::t('languages', 'error_lang_is_not_used'));
        }
        $oHub = new Hub();

        $oHub->addCommand(new delete_branch\DeleteSections($oLanguage));
        $oHub->addCommand(new delete_branch\ModuleParams($oLanguage));
        $oHub->addCommand(new delete_branch\ServiceSection($oLanguage));
        $oHub->addCommand(new delete_branch\OrderStatus($oLanguage));
        $oHub->addCommand(new delete_branch\SearchUpdate($oLanguage));

        $oHub->addCommand(new delete_branch\DeactivateLanguage($oLanguage));

        $oHub->executeOrExcept();
    }

    /**
     * Смена языка.
     *
     * @param $sOldLanguage
     * @param $sNewLanguage
     * @param $aParams
     */
    public static function swichLanguage($sOldLanguage, $sNewLanguage, $aParams)
    {
        $oHub = new Hub();

        $oInstaller = new installer\Api();

        /* Перепись системных разделов */
        $oHub->addCommand(new switch_language\ServiceSections($sOldLanguage, $sNewLanguage));

        /* Перепись параметров модулей */
        if (isset($aParams['moduleParams']) && $aParams['moduleParams']) {
            $oHub->addCommand(new switch_language\ModuleParams($sOldLanguage, $sNewLanguage));
        }

        /* Сброс поиска */
        $oHub->addCommand(new switch_language\Search());

        /* Статусы заказов */
        if ($oInstaller->isInstalled('Order', Layer::TOOL)) {
            $oHub->addCommand(new switch_language\OrderStatus($sOldLanguage, $sNewLanguage));
        }

        /* Карточка каталога */
        if ($oInstaller->isInstalled('Goods', Layer::CATALOG)) {
            if (isset($aParams['translateCard']) && $aParams['translateCard']) {
                $oHub->addCommand(new switch_language\CatalogCard($sOldLanguage, $sNewLanguage));
            }
        }

        /* Смена языка. Эта команда должна быть последней */
        $oHub->addCommand(new switch_language\SwichLanguage($sOldLanguage, $sNewLanguage));

        $oHub->executeOrExcept();
    }
}
