<?php

namespace skewer\build\Tool\Rss;

use skewer\base\queue;
use skewer\base\SysVar;
use skewer\build\Tool;

/**
 * Модуль Настроек
 * Class Module.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    protected function actionInit()
    {
        $aSections4Rss = Api::getSection4Rss();
        $aSectionsIncludedInRss = $aSections4Rss ? Api::getSectionsIncludedInRss() : [];

        $this->render(new Tool\Rss\view\Index([
            'aSections4Rss' => $aSections4Rss,
            'aSectionsIncludedInRss' => $aSectionsIncludedInRss,
            'sRssLink' => Api::getRssLink(),
            'sRssImage' => SysVar::get('Rss.image'),
        ]));
    }

    /**
     * Сохранение.
     */
    protected function actionSave()
    {
        $sOldLogo = SysVar::get('Rss.image');

        if ($this->getInDataVal('image') and ($sOldLogo !== $this->getInDataVal('image'))) {
            if (file_exists(WEBPATH . $sOldLogo)) {
                @unlink(WEBPATH . $sOldLogo);
            }
        }

        SysVar::set('Rss.image', $this->getInDataVal('image'));
        SysVar::set('Rss.sections', $this->getInDataVal('sections'));

        queue\Task::runTask(Task::getConfig(), 0, true);

        $this->actionInit();
    }
}
