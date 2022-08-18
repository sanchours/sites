<?php

namespace skewer\build\Page\CatalogViewer;

use skewer\base\section;
use skewer\base\site;
use skewer\base\SysVar;
use skewer\build\Catalog\Goods\SeoGood;
use skewer\build\Catalog\Goods\SeoGoodModifications;
use skewer\components\auth\models\GroupPolicy;
use skewer\components\auth\Policy;
use skewer\components\catalog;
use skewer\components\config\InstallPrototype;
use skewer\components\config\UpdateException;
use skewer\components\forms;
use skewer\components\forms\entities\FormLinkEntity;
use skewer\components\gallery\Profile;
use skewer\components\i18n\Languages;
use skewer\components\seo;

/**
 * Class Install.
 */
class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    // func

    /**
     * @throws UpdateException
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function install()
    {
        /* установка таблиц в бд */
        $this->buildTables();

        /* Профиль галереи. Профили должны устанавливаться раньше создания полей базовой карточки, т. к. там устанавливается профиль по умолчанию */
        $this->addGalleryProfiles();

        /** сборка данных и базовой карточки */
        $iBaseCard = catalog\Generator::genBaseCard();

        /** Расширенная карточка */
        $card = catalog\Generator::createExtCard($iBaseCard, 'dopolnitelnye_parametry', \Yii::t('data/catalog', 'cart_ext_title', [], \Yii::$app->language));
        $card->updCache();

        SysVar::set('catalog.goods_related', 1);
        SysVar::set('catalog.guest_book_show', 1);

        $aLanguages = Languages::getAllActiveNames();

        $iOrderFormSection = 0;
        foreach ($aLanguages as $sName) {
            /** Создаем форму */
            $iFormId = OrderWithoutCartEntity::createTable($sName);

            $linkNameGoods = new FormLinkEntity();
            $linkNameGoods->form_id = $iFormId;
            $linkNameGoods->form_field = 'naimenovanie-tovara';
            $linkNameGoods->card_field = 'title';
            $linkNameGoods->save();

            /** Добавление раздела с формой */
            $iOrderFormSection = $this->addSection4OrderForm($iFormId, $sName);

            /* Каталог на главной */
            $this->addCatalogOnMain($iOrderFormSection, $sName);
        }

        /* SEO template */
        $this->addSeoTemplate();

        /* Шаблон каталожного раздела */
        $this->addCatalogSectionTemplate($iOrderFormSection);

        /* соц кнопки */
        $this->addSocialButton();

        /* sysvars */
        $this->updateSysVars();

        $this->setIndex();

        /* Добавление доступов в каталог для админов */
        $this->setPolicy();

        return true;
    }

    // func

    public function uninstall()
    {
        $this->fail('Нельзя удалить каталог');

        return true;
    }

    // func

    /**
     * Изменение базы данных.
     */
    private function buildTables()
    {
        /* Атрибуты */
        catalog\model\FieldAttrTable::rebuildTable();

        /* Группы полей */
        catalog\model\FieldGroupTable::rebuildTable();

        /* Поля */
        catalog\model\FieldTable::rebuildTable();

        /* Валидаторы */
        catalog\model\ValidatorTable::rebuildTable();

        /*Таблица связи с разделами "Сопутствующие"*/
        \Yii::$app->db->createCommand('CREATE TABLE IF NOT EXISTS `related_sections` (
                                      `id` int(10) NOT NULL AUTO_INCREMENT,
                                      `target_section` int(100) NOT NULL,
                                      `related_section` int(100) NOT NULL,
    									PRIMARY KEY (`id`)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;')->execute();

        /* Сущности */
//        Query::dropTable( 'c_entity' );
        catalog\model\EntityTable::rebuildTable();

        /* Связи товаров */
        catalog\model\SectionTable::rebuildTable();
        catalog\model\GoodsTable::rebuildTable();
        catalog\model\SemanticTable::rebuildTable();

        /* Создаем таблицу руками, по другому падает с ошибкой */
        $tableName = FormLinkEntity::tableName();
        \Yii::$app->db
            ->createCommand("CREATE TABLE IF NOT EXISTS `{$tableName}` (
                        `link_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
                        `form_id` INT( 11 ) DEFAULT NULL ,
                        `form_field` VARCHAR( 255 ) DEFAULT NULL ,
                        `card_field` VARCHAR( 255 ) DEFAULT NULL ,
                        PRIMARY KEY (`link_id`)
                    ) ENGINE = MYISAM DEFAULT CHARSET = utf8;")
            ->execute();
    }

    /**
     * Добавление раздела с формой.
     *
     * @param $iForm
     * @param $sLanguage
     *
     * @throws \Exception
     * @throws UpdateException
     *
     * @return bool|int
     */
    private function addSection4OrderForm($iForm, $sLanguage)
    {
        $iTplSection = $this->addSectionByTemplate(
            \Yii::$app->sections->getValue('tools', $sLanguage),
            \Yii::$app->sections->tplNew(),
            'zayavka-na-tovar',
            \Yii::t('data/app', 'section_orderForm', [], $sLanguage),
            0
        );

        if (!$iTplSection) {
            throw new \Exception('Не удалось создать раздел для формы заказа!');
        }

        /* раздел должен быть неудаляемый! */
        $this->setParameter($iTplSection, '_break_delete', '.', '1', '', 'Системный (неудаляемый) раздел', 0);

        /*
         * добавить связь раздела с формой
         */
        forms\Api::link2Section($iForm, $iTplSection);

        $this->setServiceSections('orderForm', \Yii::t('app', 'orderForm', [], $sLanguage), $iTplSection, $sLanguage);

        return $iTplSection;
    }

    /**
     * Добавление профилей галереи для каталога.
     */
    private function addGalleryProfiles()
    {
        //профиль для основной галереи
        $iProfileId4MainGallery = Profile::setProfile([
            'type' => Profile::TYPE_CATALOG,
            'alias' => 'catalog',
            'title' => \Yii::t('data/gallery', 'profile_catalog_name', [], \Yii::$app->language),
            'active' => 1,
        ]);

        //профиль для дополнительной галереи
        $iProfileId4AddGallery = Profile::setProfile([
            'title' => \Yii::t('data/gallery', 'profile_catalog_add_name', [], \Yii::$app->language),
            'alias' => 'catalog_add',
            'type' => Profile::TYPE_CATALOG_ADD,
            'active' => 1,
            'default' => 1,
            'watermark_color' => '#ffffff',
        ]);

        if (!$iProfileId4MainGallery || !$iProfileId4AddGallery) {
            $this->fail('cant create gallery profiles');
        }
    }

    /**
     * Добавим шаблон SEO для каталожных страниц.
     */
    private function addSeoTemplate()
    {
        /* Добавляем общий для всех карточек seo - шаблон */

        if (!seo\Template::find()->where('alias', SeoGood::getAlias())->getOne()) {
            seo\Template::getNewRow(
                [
                    'alias' => SeoGood::getAlias(),
                    'name' => 'Страница товара в каталоге',
                    'title' => '[Название товара] – купить | [Название товара] по низким ценам ',
                    'description' => '[Название страницы] – широкий ассортимент. [Название товара] – купить с доставкой.',
                    'keywords' => '[название товара]',
                    'altTitle' => '[Название товара]',
                    'nameImage' => '[Название товара]',
                    'undelitable' => 1,
                ]
            )
                ->save();
        }

        /* Добавляем seo - шаблон для товаров-аналогов */
        if (!seo\Template::find()->where('alias', SeoGoodModifications::getAlias())->getOne()) {
            seo\Template::getNewRow(
                [
                    'alias' => SeoGoodModifications::getAlias(),
                    'name' => 'Страница аналогов',
                    'title' => '[Название товара]. [Название страницы]. [Цепочка разделов до главной] [Название сайта]',
                    'description' => '[Название сайта], [Цепочка разделов до страницы], [Название страницы], [Название товара]',
                    'keywords' => '[название товара], [название страницы], [цепочка разделов до главной], [Название сайта]',
                    'altTitle' => '[Название товара]',
                    'nameImage' => '[Название товара]',
                    'undelitable' => 1,
                ]
            )
                ->save();
        }
    }

    /**
     * Добавление шаблона каталожного раздела.
     *
     * @param $iOrderFormSection
     *
     * @throws \Exception
     */
    private function addCatalogSectionTemplate($iOrderFormSection)
    {
        $iTplSection = $this->addSectionByTemplate(
            \Yii::$app->sections->templates(),
            \Yii::$app->sections->tplNew(),
            'katalog',
            \Yii::t('data/catalog', 'template_catalog_section', [], \Yii::$app->language),
            1
        );

        if (!$iTplSection) {
            throw new \Exception('Не удалось создать шаблон каталожного раздела');
        }

        /*
         * накидаем параметры
         */

        /* для дизайнерского */
        $this->setParameter($iTplSection, '.title', '.layout', 'Каталог', '', 'Название шаблона расположения', 0);
        $this->setParameter($iTplSection, '.order', '.layout', '10', '', 'вес при сортировке', 0);
        $this->setParameter($iTplSection, 'content', '.layout', 'pathLine,bannerContentTop,title,staticContent,CategoryViewer,content,socialButtons,forms,staticContent2', '', 'Контент (центр)', 0);

        $this->setParameter($iTplSection, 'title', 'CatalogFilter', '', '', \Yii::t('catalogFilter', 'BlockTitle', [], \Yii::$app->language), 1);
        $this->setParameter($iTplSection, 'layout', 'CatalogFilter', 'content,left,right,head', '', '', 0);
        $this->setParameter($iTplSection, '.title', 'CatalogFilter', 'Фильтр для каталога', '', '', 0);

        $this->setParameter($iTplSection, 'relatedTpl', 'content', 'gallery', '', '', 0);
        $this->setParameter($iTplSection, 'includedTpl', 'content', 'gallery', '', '', 0);
        $this->setParameter($iTplSection, 'showFilter', 'content', 0, '', \Yii::t('catalog', 'showFilter', [], \Yii::$app->language), 0);
        $this->setParameter($iTplSection, 'template', 'content', 'list', '', \Yii::t('catalog', 'template_catalog_list_template', [], \Yii::$app->language), 0);
        $this->setParameter($iTplSection, 'object', 'content', 'CatalogViewer', '', '', 0);
        $this->setParameter($iTplSection, 'objectAdm', 'content', 'Catalog', '', '', 0);
        $this->setParameter($iTplSection, 'onPage', 'content', 12, '', \Yii::t('catalog', 'template_catalog_on_page'), 0);
        $this->setParameter($iTplSection, 'showSort', 'content', 0, '', \Yii::t('catalog', 'showSort', [], \Yii::$app->language), 0);

        // Языковой параметр раздела с формой заказа
        $this->setParameter(\Yii::$app->sections->languageRoot(), 'buyFormSection', catalog\Api::LANG_GROUP_NAME, $iOrderFormSection, '', \Yii::t('catalog', 'template_catalog_order_form', [], \Yii::$app->language), 0);
        $this->setParameter(\Yii::$app->sections->tplNew(), 'buyFormSection', catalog\Api::LANG_GROUP_NAME, '', '', \Yii::t('catalog', 'template_catalog_order_form', [], \Yii::$app->language), section\params\Type::paramLanguage);
    }

    /**
     * Добавление каталога на главной.
     *
     * @param $iFormSection
     * @param $sLanguage
     */
    private function addCatalogOnMain($iFormSection, $sLanguage)
    {
        $iMainSection = \Yii::$app->sections->getValue('main', $sLanguage);

        $this->setParameter($iMainSection, '.title', 'catalog', 'catalog.catalog_title', '', 'Название шаблона расположения', 0);
        $this->setParameter($iMainSection, 'layout', 'catalog', 'content', '', '', 0);
        $this->setParameter($iMainSection, 'object', 'catalog', 'CatalogViewer', '', '', 0);
        $this->setParameter($iMainSection, 'onMain', 'catalog', 'on_main', '', 'Вывод на главной', 0);
        $this->setParameter($iMainSection, 'onPage', 'catalog', '3', '', \Yii::t('catalog', 'param_on_page', [], \Yii::$app->language), 0);
        $this->setParameter($iMainSection, 'titleOnMain', 'catalog', 'Каталог', '', \Yii::t('catalog', 'param_title_on_main', [], $sLanguage), 0);
        $this->setParameter($iMainSection, 'template', 'catalog', 'gallery', 'list ' . \Yii::t('editor', 'type_catalog_list', [], $sLanguage) . "\n"
            . 'gallery ' . \Yii::t('editor', 'type_catalog_gallery', [], $sLanguage), \Yii::t('editor', 'type_catalog_view', [], $sLanguage), 0);

        $this->setParameter($iMainSection, '.title', 'catalog2', 'catalog.catalog_hits', '', 'Название шаблона расположения', 0);
        $this->setParameter($iMainSection, 'layout', 'catalog2', 'content', '', '', 0);
        $this->setParameter($iMainSection, 'object', 'catalog2', 'CatalogViewer', '', '', 0);
        $this->setParameter($iMainSection, 'onMain', 'catalog2', 'hit', '', 'Вывод на главной', 0);
        $this->setParameter($iMainSection, 'onPage', 'catalog2', '3', '', \Yii::t('catalog', 'param_on_page', [], \Yii::$app->language), 0);
        $this->setParameter($iMainSection, 'titleOnMain', 'catalog2', 'Hits', '', \Yii::t('catalog', 'param_title_on_main', [], $sLanguage), 0);
        $this->setParameter($iMainSection, 'template', 'catalog2', 'gallery', 'list ' . \Yii::t('editor', 'type_catalog_list', [], $sLanguage) . "\n"
            . 'gallery ' . \Yii::t('editor', 'type_catalog_gallery', [], $sLanguage), \Yii::t('editor', 'type_catalog_view', [], $sLanguage), 0);

        $this->setParameter($iMainSection, '.title', 'catalog3', 'catalog.catalog_news', '', 'Название шаблона расположения', 0);
        $this->setParameter($iMainSection, 'layout', 'catalog3', 'content', '', '', 0);
        $this->setParameter($iMainSection, 'object', 'catalog3', 'CatalogViewer', '', '', 0);
        $this->setParameter($iMainSection, 'onMain', 'catalog3', 'new', '', 'Вывод на главной', 0);
        $this->setParameter($iMainSection, 'onPage', 'catalog3', '3', '', \Yii::t('catalog', 'param_on_page', [], \Yii::$app->language), 0);
        $this->setParameter($iMainSection, 'titleOnMain', 'catalog3', 'News', '', \Yii::t('catalog', 'param_title_on_main', [], $sLanguage), 0);
        $this->setParameter($iMainSection, 'template', 'catalog3', 'gallery', 'list ' . \Yii::t('editor', 'type_catalog_list', [], $sLanguage) . "\n"
            . 'gallery ' . \Yii::t('editor', 'type_catalog_gallery', [], $sLanguage), \Yii::t('editor', 'type_catalog_view', [], $sLanguage), 0);
    }

    /**
     * Добавляем значения нужных полей в sysvar.
     */
    private function updateSysVars()
    {
        SysVar::set('site_type', site\Type::catalog);
        SysVar::set('catalog.random_related_count', 4);
    }

    /**
     * Добавление параметра о соц. кнопке.
     */
    private function addSocialButton()
    {
        $this->setParameter(\Yii::$app->sections->root(), 'soclinkGoods', 'socialButtons', '', '', 'SocialButtons.social_catalog', -5);
    }

    /**
     * Добавление доступа в панель каталога.
     */
    private function setPolicy()
    {
        $aPolicy = GroupPolicy::find()
            ->where(['!=', 'access_level', 0])
            ->andWhere(['area' => 'admin'])
            ->asArray()
            ->all();

        if ($aPolicy) {
            foreach ($aPolicy as $aItem) {
                Policy::setGroupActionParam(
                    $aItem['id'],
                    'skewer\build\Catalog\LeftList\Module',
                    'useCatalog',
                    1
                );
            }
        }
    }

    private function setIndex()
    {
        //индекс для таблицы cl_section
        \Yii::$app->db->createCommand('ALTER TABLE ' . \skewer\components\catalog\model\SectionTable::getTableName() . '  ADD INDEX goods_id (goods_id);')->execute();
        \Yii::$app->db->createCommand('ALTER TABLE ' . \skewer\components\catalog\model\SectionTable::getTableName() . '  ADD INDEX goods_card (goods_id,goods_card);')->execute();
        \Yii::$app->db->createCommand('ALTER TABLE ' . \skewer\components\catalog\model\SectionTable::getTableName() . '  ADD INDEX section_id (section_id,priority);')->execute();

        //индекс для таблицы c_goods
        \Yii::$app->db->createCommand('ALTER TABLE ' . \skewer\components\catalog\model\GoodsTable::getTableName() . '  ADD INDEX base_id (base_id);')->execute();
        \Yii::$app->db->createCommand('ALTER TABLE ' . \skewer\components\catalog\model\GoodsTable::getTableName() . '  ADD INDEX parent  (parent );')->execute();

        //индекс для таблицы c_goods
        \Yii::$app->db->createCommand('ALTER TABLE co_' . \skewer\components\catalog\Card::DEF_BASE_CARD . '  ADD INDEX article (article);')->execute();
    }
}// class
