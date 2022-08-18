<?php

namespace skewer\build\Adm\FAQ;

use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\build\Adm\FAQ\models\Faq;
use skewer\components\config\InstallPrototype;
use skewer\components\i18n\Languages;
use skewer\components\i18n\ModulesParams;
use skewer\components\seo\Template;
use skewer\components\seo\TemplateRow;
use yii\helpers\ArrayHelper;

class Install extends InstallPrototype
{
    private $moduleParamKeys = [
        'title_admin', 'content_admin',
        'title_user', 'content_user', 'onNotif',
        'notifTitleApprove', 'notifContentApprove', 'notifTitleReject',
        'notifContentReject',
    ];

    public function init()
    {
        return true;
    }

    // func

    public function install()
    {
        /* Перестройка таблиц */
        \Yii::$app->db->createCommand(
            "CREATE TABLE IF NOT EXISTS `faq` (
          `id` bigint(20) NOT NULL AUTO_INCREMENT,
          `parent` int(11) NOT NULL,
          `date_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
          `name` varchar(255) NOT NULL,
          `email` varchar(255) NOT NULL,
          `content` text NOT NULL,
          `status` int(3) NOT NULL,
          `city` varchar(255) NOT NULL,
          `answer` text NOT NULL,
          `alias` varchar(255) NOT NULL,
          `last_modified_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `date_modify` (`last_modified_date`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;"
        )->execute();

        /** Шаблонный раздел */
        $iTplSectionId = $this->addSectionByTemplate(\Yii::$app->sections->templates(), \Yii::$app->sections->tplNew(), 'faq', 'Вопросы-Ответы');

        $this->setParameter($iTplSectionId, 'object', 'content', 'FAQ');
        $this->setParameter($iTplSectionId, 'objectAdm', 'content', 'FAQ');

        $this->setParameter($iTplSectionId, 'object', 'forms', '');
        $this->setParameter($iTplSectionId, 'objectAdm', 'forms', '');

        foreach (ArrayHelper::map(Languages::getAllActive(), 'name', 'name') as $lang) {
            foreach ($this->moduleParamKeys as $key) {
                ModulesParams::setParams('data/faq', $key, $lang, \Yii::t('faq', $key, [], $lang));
            }
        }

        $this->addSeoTemplate();

        return true;
    }

    // func

    public function uninstall()
    {
        $iTplSections = Tree::getSectionByAlias('faq', \Yii::$app->sections->templates());

        if ($iTplSections !== null) {
            $aSections = Parameters::getChildrenList($iTplSections);

            if ($aSections !== false) {
                foreach ($aSections as $iSection) {
                    Tree::removeSection($iSection);
                }
            }

            Tree::removeSection($iTplSections);
        }

        ModulesParams::deleteByModule('faq');

        $this->deleteSeoTemplate();

        // удаление основной таблицы
        \Yii::$app->db->createCommand()->dropTable(Faq::tableName())->execute();

        return true;
    }

    // func

    /** Добавление seo-шаблона */
    public function addSeoTemplate()
    {
        /** @var TemplateRow $oFaqTpl */
        $oFaqTpl = Template::getNewRow();
        $oFaqTpl->alias = Seo::getAlias();
        $oFaqTpl->name = 'Детальная страница вопроса';
        $oFaqTpl->title = '[Название вопроса] – [Название сайта]';
        $oFaqTpl->description = '[Название сайта] – [Название страницы]. [Название вопроса]';
        $oFaqTpl->keywords = '[название вопроса]';
        $oFaqTpl->altTitle = '';
        $oFaqTpl->nameImage = '';
        $oFaqTpl->save();
    }

    /** Удаление seo-шаблона */
    public function deleteSeoTemplate()
    {
        Template::delete()->where('alias', Seo::getAlias())->get();
    }

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
