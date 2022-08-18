<?php

namespace skewer\build\Cms\Header;

use skewer\base\site_module\Context;
use skewer\build\Cms;
use skewer\components\auth\CurrentAdmin;

/**
 * Class Module.
 */
class Module extends Cms\Frame\ModulePrototype
{
    protected function actionInit()
    {
        $this->addInitParam('renderData', [
            'href' => '/oldadmin/',
            'logoImg' => $this->getModuleWebDir() . '/img/canape_cms_logo.png',
        ]);

        $this->addInitParam('SelectDesign', $this->getSelectDesignData());

        $this->addInitParam('lang', $this->parseLangVars(['link_help']));

        // добавить панель авторизации
        $this->addChildProcess(new Context('auth', 'skewer\build\Cms\Auth\Module', ctModule, []));

        $this->addChildProcess(new Context('lang', 'skewer\build\Cms\Lang\Module', ctModule, []));

        $this->addChildProcess(new Context('messages', 'skewer\build\Cms\Messages\Module', ctModule, []));

        if (CurrentAdmin::isSystemMode()) {
            $this->addChildProcess(new Context('cache', 'skewer\build\Cms\Cache\Module', ctModule, []));
        }

        if (\Yii::$app->register->moduleExists('Search', 'Cms')) {
            $this->addChildProcess(new Context('search', 'skewer\build\Cms\Search\Module', ctModule, []));
        }

        return psComplete;
    }

    /**
     * @return array[]
     */
    protected function getSelectDesignData(): array
    {
        return [
            [
                'title' => \Yii::t('adm', 'Header.title_design_link'),
                'href' => '/design/',
            ],
            [
                'title' => \Yii::t('adm', 'Header.title_design_params_link'),
                'href' => '/design/?mode=editor',
            ]
        ];
    }
}