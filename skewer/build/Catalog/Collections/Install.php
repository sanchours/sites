<?php

namespace skewer\build\Catalog\Collections;

use skewer\base\section\Parameters;
use skewer\components\config\InstallPrototype;
use skewer\components\gallery\Profile;
use skewer\components\seo;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    public function install()
    {
        // добавляем новый профиль форматов
        $profile_id = Profile::setProfile([
            'type' => Profile::TYPE_CATALOG4COLLECTION,
            'alias' => 'collection',
            'title' => \Yii::t('data/gallery', 'profile_collection_name', [], \Yii::$app->language),
            'active' => 1,
        ]);

        if (!$profile_id) {
            $this->fail('cant create gallery profile');
        }

        $this->addSeoTemplates();

        // добавление модуля для вывода на главной
        $group = 'collection';
        Parameters::setParams(\Yii::$app->sections->main(), $group, '.title', 'Коллекции каталога');
        Parameters::setParams(\Yii::$app->sections->main(), $group, 'layout', 'content');
        Parameters::setParams(\Yii::$app->sections->main(), $group, 'titleOnMain', 'Catalog collection', '', \Yii::t('catalog', 'param_title_on_main', [], \Yii::$app->language));
        Parameters::setParams(\Yii::$app->sections->main(), $group, 'object', 'CatalogViewer');
        Parameters::setParams(\Yii::$app->sections->main(), $group, 'onMainCollection', '1');
        Parameters::setParams(\Yii::$app->sections->main(), $group, 'template', 'list', 'list editor.type_collection_list' . "\n"
            . 'slider editor.type_collection_slider', \Yii::t('editor', 'type_category_view', [], \Yii::$app->language));

        return true;
    }

    public function uninstall()
    {
        $this->fail('Нельзя удалить компонент каталога');

        return true;
    }

    /** Добавление seo-шаблонов */
    public function addSeoTemplates()
    {
        $tpl = new seo\TemplateRow();
        $tpl->alias = SeoCollectionList::getAlias();
        $tpl->name = 'Страница коллекции в каталоге';
        $tpl->title = '[Название коллекции] – [Название страницы]. [Название сайта]';
        $tpl->description = '[Название сайта] – [Название страницы].  [Название коллекции]';
        $tpl->keywords = '[название коллекции]';
        $tpl->altTitle = '';
        $tpl->nameImage = '';
        $tpl->undelitable = 1;
        $tpl->save();

        $tpl = new seo\TemplateRow();
        $tpl->alias = SeoElementCollection::getAlias();
        $tpl->name = 'Страница элемента коллекции в каталоге';
        $tpl->title = '[Элемент коллекции] – [Название коллекции]. [Название страницы]. [Название сайта]';
        $tpl->description = '[Название коллекции] – [Элемент коллекции]. [Название сайта]';
        $tpl->keywords = '[название страницы]';
        $tpl->altTitle = '[Название коллекции] – [Элемент коллекции]';
        $tpl->nameImage = '';
        $tpl->undelitable = 1;
        $tpl->save();
    }
}
