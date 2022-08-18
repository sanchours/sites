<?php

namespace skewer\build\Page\CategoryViewer;

use skewer\build\Adm\CategoryViewer\Seo;
use skewer\components\config\InstallPrototype;
use skewer\components\seo\Template;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    public function install()
    {
        $sGroupParam = 'CategoryViewer';

        $iMainTplId = \Yii::$app->sections->tplNew();

        $this->addParameter($iMainTplId, 'object', 'CategoryViewer', '', $sGroupParam);
        $this->addParameter($iMainTplId, '.title', 'categoryViewer.title', '', $sGroupParam);
        $this->addParameter($iMainTplId, 'layout', 'content', '', $sGroupParam);

        $this->addSeoTemplate();

        return true;
    }

    public function uninstall()
    {
        $sGroupParam = 'CategoryViewer';

        $iMainTplId = \Yii::$app->sections->tplNew();

        $this->removeParameter($iMainTplId, 'object', $sGroupParam);
        $this->removeParameter($iMainTplId, '.title', $sGroupParam);
        $this->removeParameter($iMainTplId, 'layout', $sGroupParam);

        $this->deleteSeoTemplate();

        return true;
    }

    /**
     * Добавит seo шаблон разводки.
     */
    private function addSeoTemplate()
    {
        \Yii::$app->db
            ->createCommand("INSERT INTO 
            `seo_templates` (`id`, `name`, `title`, `description`, `keywords`, `altTitle`, `nameImage`, `info`, `alias`, `extraalias`, `undelitable`) 
            VALUES (NULL, 'Элемент разводки', '', '', '', '[Название страницы]', 'Фото [название страницы]', '', 'categoryViewerElement', '', '1')")
            ->execute();
    }

    /**
     * Удалит seo шаблон разводки.
     */
    private function deleteSeoTemplate()
    {
        if ($oSeoTemplate = Template::findOne(['alias' => Seo::getAlias()])) {
            $oSeoTemplate->delete();
        }
    }
}
