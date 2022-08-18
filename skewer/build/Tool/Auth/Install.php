<?php

namespace skewer\build\Tool\Auth;

use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\base\SysVar;
use skewer\build\Page\Auth\Api;
use skewer\build\Page\Main;
use skewer\components\auth\Policy;
use skewer\components\config\InstallPrototype;
use skewer\components\config\UpdateException;
use skewer\components\i18n\Languages;
use skewer\components\i18n\ModulesParams;
use skewer\components\seo;
use yii\helpers\ArrayHelper;

class Install extends InstallPrototype
{
    /**@param array() Массив меток для писем клиенту */
    private $moduleParamKeys = [
        'mail_activate',
        'mail_close_ban',
        'mail_banned',
        'mail_admin_activate',
        'mail_user_activate',
        'mail_title_admin_newuser',
        'mail_title_user_newuser',
        'mail_title_mail_activate',
        'mail_title_mail_close_banned',
        'mail_title_mail_banned',
    ];

    private $languages;

    public function init()
    {
        $this->languages = ArrayHelper::map(Languages::getAllActive(), 'name', 'name');

        return true;
    }

    // func

    public function install()
    {
        /* Установка параметров модуля */
        $this->setModuleParams();

        /* Установка параметров в шаблоны */
        $this->setTemplateParams();

        /* Создание разделов авторизации */
        $this->createAuthSections();

        /* тип активации по умолчанию */
        SysVar::set('auth.activate_status', Api::ACTIVATE_EMAIL);
        Policy::addModule(Policy::POLICY_ADMIN_USERS, $this->getModuleName(), $this->config->getTitle());

        return true;
    }

    // func

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function uninstall()
    {
        foreach (\Yii::$app->sections->getValues('auth') as $value) {
            Tree::removeSection($value);
        }

        ModulesParams::deleteByModule('auth');
        Policy::removeModule(Policy::POLICY_ADMIN_USERS, $this->getModuleName());

        return true;
    }

    protected function setModuleParams()
    {
        foreach ($this->languages as $lang) {
            foreach ($this->moduleParamKeys as $key) {
                ModulesParams::setParams('auth', $key, $lang, \Yii::t('data/auth', $key, [], $lang));
            }
        }
    }

    /**
     * @throws UpdateException
     */
    protected function setTemplateParams()
    {
        $iNewPageSection = \Yii::$app->sections->tplNew();
        $this->addParameter($iNewPageSection, 'layout', 'left,right', '', 'auth');
        $this->addParameter($iNewPageSection, 'mini_auth', '1', '', 'auth');
        $this->addParameter($iNewPageSection, 'object', 'Auth', '', 'auth');
    }

    protected function createAuthSections()
    {
        $iNewPageSection = \Yii::$app->sections->tplNew();
        foreach (\Yii::$app->sections->getValues('tools') as $sLang => $iSection) {
            $oSection = Tree::addSection($iSection, \Yii::t('data/auth', 'auth_sections_title', [], $sLang), $iNewPageSection, 'auth', Visible::HIDDEN_FROM_MENU);

            /*Закроем от индексации*/
            seo\Api::set(Main\Seo::getGroup(), $oSection->id, $oSection->id, ['none_index' => 1]);

            $this->setParameter($oSection->id, 'object', 'auth', '');
            $this->setParameter($oSection->id, 'object', 'content', 'Auth');
            $this->setParameter($oSection->id, 'objectAdm', 'content', 'Auth');

            \Yii::$app->sections->setSection('auth', \Yii::t('site', 'auth', [], $sLang), $oSection->id, $sLang);
        }
    }

    // func

    public function getCommandsAfterInstall()
    {
        return [
            '\\skewer\\components\\config\\installer\\Service:rebuildSearchIndex',
            '\\skewer\\components\\config\\installer\\Service:resetActive',
            '\\skewer\\components\\config\\installer\\Service:makeSearchIndex',
            '\\skewer\\components\\config\\installer\\Service:makeSiteMap',
        ];
    }
}//class
