<?php
/**
 * Created by PhpStorm.
 * User: ram
 * Date: 15.05.2018
 * Time: 15:34.
 */

namespace skewer\build\Tool\RobotsTxt\view;

use skewer\base\ft\Editor;
use skewer\build\Tool;
use skewer\components\ext\docked;
use skewer\components\ext\view\FormView;
use skewer\components\seo\Service;

class Index extends FormView
{
    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->headText('<h2>' . \Yii::t('robotstxt', 'field_robots_content') . '</h2>' . \Yii::t('robotstxt', 'subtext'))
            ->field('robots_content', \Yii::t('robotstxt', 'field_robots_content'), Editor::TEXT, [
                'height' => '100%',
                'hideLabel' => true,
            ]);

        $contentOveriden = Tool\RobotsTxt\Api::getSysVar('content_overridden', '0');

        if ($contentOveriden === '1') {
            $this->_form->button('RebuildRobots', \Yii::t('robotstxt', 'rebuildRobots'), docked\Api::iconReload, 'allow_do', ['actionText' => \Yii::t('robotstxt', 'rebuild_warningtext')]);
        } else {
            $this->_form->button('RebuildRobots', \Yii::t('robotstxt', 'rebuildRobots'), docked\Api::iconReload);
        }

        if ($contentOveriden === '1') {
            $robotsContent = Tool\RobotsTxt\Api::getSysVar('robots_content', '');
        } else {
            $robotsContent = Service::generateDefaultContentRobotsTxtFile(Tool\Domains\Api::getMainDomain(), true);
        }

        $this->_form
            ->buttonSave()
            ->setValue(['robots_content' => $robotsContent]);
    }
}
