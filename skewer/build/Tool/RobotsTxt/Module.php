<?php

namespace skewer\build\Tool\RobotsTxt;

use skewer\build\Tool;
use skewer\build\Tool\Domains;
use skewer\components\seo\Service;
use yii\base\UserException;

/**
 * Модуль редактирования файла robots.txt
 * Class Module.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    protected function actionInit()
    {
        $this->render(new view\Index([]));
    }

    protected function actionSave()
    {
        $sContent = $this->getInDataVal('robots_content', '');

        if (preg_match('/(Host|Sitemap)\S*:/i', $sContent) === 1) {
            throw new UserException(\Yii::t('robotstxt', 'subtext'));
        }
        // сохранить данные
        Api::setSysVar('robots_content', $sContent);

        // пересобрать robots.txt
        $bRes = Service::updateRobotsTxt(Domains\Api::getMainDomain());

        if ($bRes) {
            Api::setSysVar('content_overridden', '1');
            $this->addMessage('', \Yii::t('robotstxt', 'robots_update_msg'));
        } else {
            $this->addError('', \Yii::t('robotstxt', 'robots_update_error'));
        }

        $this->actionInit();
    }

    /**
     * Перестроим роботс
     */
    protected function actionRebuildRobots()
    {
        Tool\RobotsTxt\Api::setSysVar('content_overridden', '0');

        $res = Service::updateRobotsTxt(Domains\Api::getMainDomain());

        if ($res) {
            $this->addMessage('', \Yii::t('robotstxt', 'robots_update_msg'));
        } else {
            $this->addError('', \Yii::t('robotstxt', 'robots_update_error'));
        }

        $this->actionInit();
    }
}
