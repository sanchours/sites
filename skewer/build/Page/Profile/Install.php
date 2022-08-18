<?php

declare(strict_types=1);

namespace skewer\build\Page\Profile;

use skewer\base\ft\Exception;
use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\build\Page\Main\Seo;
use skewer\components\config\InstallPrototype;
use skewer\components\forms\service\FormService;
use skewer\components\seo\Api;

class Install extends InstallPrototype
{
    /** @var FormService $_formService */
    private $_formService;

    public function init()
    {
        $this->_formService = new FormService();

        return true;
    }

    // func

    /**
     * @throws Exception
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function install()
    {
        $this->createProfileSections();

        /* Создание формы редактирования профиля*/
        if (!$this->_formService->hasFormWithSlug(EditProfileEntity::tableName())) {
            EditProfileEntity::createTable();
        }

        if (!$this->_formService->hasFormWithSlug(NewPassEntity::tableName())) {
            NewPassEntity::createTable();
        }

        return true;
    }

    // func

    public function uninstall()
    {
        return true;
    }

    protected function createProfileSections()
    {
        $iNewPageSection = \Yii::$app->sections->tplNew();
        foreach (\Yii::$app->sections->getValues('tools') as $sLang => $iSection) {
            $oSection = Tree::addSection(
                $iSection,
                \Yii::t('data/auth', 'profile_sections_title', [], $sLang),
                $iNewPageSection,
                'profile',
                Visible::HIDDEN_FROM_MENU
            );
            $this->setParameter($oSection->id, 'object', 'content', 'Profile');
            $this->setParameter(
                $oSection->id,
                'right',
                '.layout',
                '{show_val}',
                '',
                'editor.right_column',
                0
            );

            /*Закроем от индексации*/
            Api::set(
                Seo::getGroup(),
                $oSection->id,
                $oSection->id,
                ['none_index' => 1]
            );

            \Yii::$app->sections->setSection(
                'profile',
                \Yii::t('site', 'profile', [], $sLang),
                $oSection->id,
                $sLang
            );
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
}// class
