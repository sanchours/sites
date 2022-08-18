<?php

namespace skewer\build\Page\Main\templates\head\head_shop;

use skewer\base\section\params\Type;
use skewer\components\design\Block;
use skewer\components\design\TplSwitchHead;
use skewer\helpers\Adaptive;

class Switcher extends TplSwitchHead
{
    public $bUse = true;

    public $sPathDir = '/files/head_shop/images';

    /**
     * Отдает имя название шаблона.
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Магазин';
    }

    /**
     * Отдает набор меток модулей, которые должны быть выведены в шапку.
     */
    protected function getModulesList()
    {
        return 'authHead,searchHead,AdaptiveMode,topMenu,minicartHead,emptyHeadBlock,mainBanner';
    }

    /**
     * Задать набор настроек для модулей.
     */
    public function setModuleSettings()
    {
        $this->setModulesToLayout('adaptive_menu', 'authHead,minicartHead,headtext1,adaptive_menu_topMenu,adaptive_menu_leftMenu,adaptive_menu_catalogMenu');

        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'AdaptiveMode',
            'headTpl',
            'head_shop.php'
        );

        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'AdaptiveMode',
            'contentTpl',
            'content_shop.php'
        );

        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'minicartHead',
            'template',
            'head-bilberry.twig'
        );

        // Авторизация
        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'authHead',
            'miniAuthHeadTpl',
            'AuthFormMiniHeadDropdown.twig'
        );

        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'authHead',
            'dropDown',
            '1'
        );

        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'topMenu',
            'templateFile',
            'topMenuShop.twig'
        );
        // доп меню
        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'subMenu',
            'object',
            'Menu'
        );
        // для доп меню расрываем все уровни
        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'subMenu',
            'openAll',
            '1'
        );
        // задаем для доп меню шаблон
        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'subMenu',
            'templateFile',
            'topMenuShopSubMenu.twig'
        );
        // задаем для доп меню что выводить
        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'subMenu',
            'parentSection',
            'leftMenu',
            null,
            null,
            Type::paramServiceSection
        );
        // выводим левое меню в верхнее
        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'topMenu',
            'subModules',
            'subMenu'
        );
    }

    /**
     * Задать настройки для типовых блоков.
     */
    public function setBlocks()
    {
        /*-Шапка сайта-*/

        $this->setCssVal('page.head.img.height', '130px');
        $this->setCssVal('page.head.color_a', '#4381cd');
        $this->setCssVal('page.head.img.color', '#fff');

        /*-Блоки в шапке-*/

        $this->setCssVal('page.head.logo.h_value', '20px');
        $this->setCssVal('page.head.logo.v_value', '100px');

        $this->setCssVal('page.head.pilot1.h_position', 'right');
        $this->setCssVal('page.head.pilot1.h_value', '230px');
        $this->setCssVal('page.head.pilot1.v_value', '105px');
        $this->setCssVal('page.head.pilot1.width', '200px');

        $this->setCssVal('page.head.pilot2.h_position', 'left');
        $this->setCssVal('page.head.pilot2.h_value', '240px');
        $this->setCssVal('page.head.pilot2.v_value', '100px');
        $this->setCssVal('page.head.pilot2.width', '170px');

        $this->setCssVal('page.head.pilot3.h_value', '100%');
        $this->setCssVal('page.head.pilot4.h_value', '100%');
        $this->setCssVal('page.head.pilot5.h_value', '100%');

        /*-Верхнее меню-*/

        $this->setCssVal('menu.top.position', 'left');

        $this->setCssVal('menu.top.level1.level1_bg.color', '#1f84d3');
        $this->setCssVal('menu.top.level1.level1_bg.padding', '0');

        $this->setCssVal('menu.top.level1.normal.bullit.img', $this->sPathDir . '/menutop-mar.png');
        $this->setCssVal('menu.top.level1.normal.bullit.hvalue', '0');
        $this->setCssVal('menu.top.level1.normal.bullit.vvalue', '0');
        $this->setCssVal('menu.top.level1.normal.bullit.height', '100%');
        $this->setCssVal('menu.top.level1.normal.bullit.width', '30px');
        $this->setCssVal('menu.top.level1.normal.background.color', 'transparent');
        $this->setCssVal('menu.top.level1.normal.background.padding', '23px 30px');
        $this->setCssVal('menu.top.level1.normal.link.size', '14px');
        $this->setCssVal('menu.top.level1.normal.link.transform', 'none');
        $this->setCssVal('menu.top.level1.normal.link.color', '#fff');

        $this->setCssVal('menu.top.level1.active.bullit.img', $this->sPathDir . '/menutop-mar-on.png');
        $this->setCssVal('menu.top.level1.active.bullit.hvalue', '0');
        $this->setCssVal('menu.top.level1.active.bullit.vvalue', '0');
        $this->setCssVal('menu.top.level1.active.bullit.height', '100%');
        $this->setCssVal('menu.top.level1.active.bullit.width', '30px');
        $this->setCssVal('menu.top.level1.active.background.color', '#2b2b2b');
        $this->setCssVal('menu.top.level1.active.background.padding', '23px 30px');
        $this->setCssVal('menu.top.level1.active.link.size', '14px');
        $this->setCssVal('menu.top.level1.active.link.transform', 'none');
        $this->setCssVal('menu.top.level1.active.link.color', '#fff');

        $this->setCssVal('menu.top.level2.width', '250px');

        $this->setCssVal('menu.top.level2.normal.background.color', '#2b2b2b');
        $this->setCssVal('menu.top.level2.normal.background.padding', '10px 30px');
        $this->setCssVal('menu.top.level2.normal.link.size', '14px');
        $this->setCssVal('menu.top.level2.normal.link.transform', 'none');
        $this->setCssVal('menu.top.level2.normal.link.color', '#7ac5ff');
        $this->setCssVal('menu.top.level2.normal.bullit.width', '30px');
        $this->setCssVal('menu.top.level2.normal.bullit.height', '30px');
        $this->setCssVal('menu.top.level2.normal.bullit.vvalue', '16px');

        $this->setCssVal('menu.top.level2.active.background.color', '#2b2b2b');
        $this->setCssVal('menu.top.level2.active.background.padding', '10px 30px');
        $this->setCssVal('menu.top.level2.active.link.size', '14px');
        $this->setCssVal('menu.top.level2.active.link.transform', 'none');
        $this->setCssVal('menu.top.level2.active.link.color', '#fff');
        $this->setCssVal('menu.top.level2.active.bullit.width', '30px');
        $this->setCssVal('menu.top.level2.active.bullit.height', '30px');
        $this->setCssVal('menu.top.level2.active.bullit.vvalue', '16px');

        $this->setCssVal('menu.top.level3.width', '250px');

        $this->setCssVal('menu.top.level3.normal.background.color', '#2b2b2b');
        $this->setCssVal('menu.top.level3.normal.background.padding', '10px 30px');
        $this->setCssVal('menu.top.level3.normal.link.size', '14px');
        $this->setCssVal('menu.top.level3.normal.link.transform', 'none');
        $this->setCssVal('menu.top.level3.normal.link.color', '#7ac5ff');

        $this->setCssVal('menu.top.level3.active.background.color', '#2b2b2b');
        $this->setCssVal('menu.top.level3.active.background.padding', '10px 30px');
        $this->setCssVal('menu.top.level3.active.link.size', '14px');
        $this->setCssVal('menu.top.level3.active.link.transform', 'none');
        $this->setCssVal('menu.top.level3.active.link.color', '#fff');

        /*-Корзина-*/

        $this->setCssVal('modules.basketmain.padding_l', '80px');
        $this->setCssVal('modules.basketmain.backimg', $this->sPathDir . '/icon-cart.png');
        $this->setCssVal('modules.basketmain.backcolor', '#4eb2ff');
        $this->setCssVal('modules.basketmain.bordercolor', 'transparent');
        $this->setCssVal('modules.basketmain.backhposition', '0');
        $this->setCssVal('modules.basketmain.backvposition', '50%');
        $this->setCssVal('modules.basketmain.height', '60px');
        $this->setCssVal('modules.basketmain.head.width', '210px');
        $this->setCssVal('modules.basketmain.color', '#fff');
        $this->setCssVal('modules.basketmain.head.v_value', '35px');
        $this->setCssVal('modules.basketmain.head.h_position', 'right');
        $this->setCssVal('modules.basketmain.head.h_value', '20px');

        /*-Поиск-*/

//        $this->setCssVal( 'modules.search.btn_img', '20px' );
        $this->setCssVal('modules.search.btn_width', '60px');
        $this->setCssVal('modules.search.head.bgcolor', 'transparent');
        $this->setCssVal('modules.search.head.textcolor', '#000');

        /*-Форма авторизации-*/

        $this->setCssVal('modules.auth.authmain.bgcolor', 'transparent');
        $this->setCssVal('modules.auth.authmain.bordercolor', 'transparent');
        $this->setCssVal('modules.auth.authmain.marginb', '0');
        $this->setCssVal('modules.auth.authmain.backcolor', '#3198e8');

        if (Adaptive::modeIsActive()) {
            /*-Адаптивный режим-*/

            $this->setCssVal('adaptive.sidebar.icon_close.pic', $this->sPathDir . '/sidebar-close.png');
            $this->setCssVal('adaptive.sidebar.icon_close.background_color_block', '#1e1e1e');
            $this->setCssVal('adaptive.sidebar.icon_close.hor_pos', 'left');
            $this->setCssVal('adaptive.sidebar.icon_close.height', '60px');

            $this->setCssVal('adaptive.sidebar.icon_sandwich.pic', $this->sPathDir . '/icon-sandwich.png');
            $this->setCssVal('adaptive.sidebar.icon_sandwich.height', '60px');

            $this->setCssVal('adaptive.sidebar.params.background_color', '#1e1e1e');

            $this->setCssVal('adaptive.side_menu.separ_color', '#1e1e1e');
            $this->setCssVal('adaptive.sidebar.params.link_color', '#fff');
            $this->setCssVal('adaptive.side_menu.mar_open', $this->sPathDir . '/menuside-mar.png');
            $this->setCssVal('adaptive.side_menu.mar_close', $this->sPathDir . '/menuside-mar-on.png');
        }
    }

    /**
     * Установить типовой контент
     */
    public function setContent()
    {
        $this->copyDirFiles('web/images', $this->sPathDir);

        $this->setLogo($this->sPathDir . '/logo-shop.png');

        $iCallbackSection = \Yii::$app->sections->getValue('callback');

        $this->setBlockText(Block::pilot1, '<div class="b-head-phone"><a href="tel:+79000000000">+7 (900) 000 00 00</a></div><div class="b-head-callback"><a class="js-callback" data-ajaxform="1" data-section="' . $iCallbackSection . '" data-js_max_width="600" data-width-type="px" href="#">Заказать обратный звонок</a></div>');
        $this->setBlockText(Block::pilot2, '<div class="b-logo-text hide-on-tablet hide-on-mobile"><a href="/">Интернет-магазин экологически чистых продуктов питания</a></div>');
        $this->setBlockText(Block::pilot3, '');
        $this->setBlockText(Block::pilot4, '');
        $this->setBlockText(Block::pilot5, '');
    }
}
