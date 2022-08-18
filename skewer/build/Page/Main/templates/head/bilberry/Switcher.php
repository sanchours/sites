<?php

namespace skewer\build\Page\Main\templates\head\bilberry;

use skewer\components\design\Block;
use skewer\components\design\Design;
use skewer\components\design\TplSwitchHead;
use skewer\helpers\Adaptive;

class Switcher extends TplSwitchHead
{
    public $sPathDir = '/files/bilberry/images';

    /**
     * Отдает имя название шаблона.
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Черника';
    }

    /**
     * Отдает набор меток модулей, которые должны быть выведены в шапку.
     */
    protected function getModulesList()
    {
        $sAdapt = Design::modeIsActive() ? 'AdaptiveMode' : '';

        return "authHead,searchHead,{$sAdapt},topMenu,minicartHead,emptyHeadBlock,topMenu2,mainBanner";
    }

    /**
     * Задать набор настроек для модулей.
     */
    public function setModuleSettings()
    {
        // Переопределение лейаута для сайдбара
        $this->setModulesToLayout('adaptive_menu', 'authHead,headtext1,headtext2,adaptive_menu_topMenu,adaptive_menu_leftMenu');

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
            'minicartHead',
            'template',
            'head-bilberry.twig'
        );

        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'topMenu',
            'templateFile',
            'topMenuDropdown.twig'
        );

        // создать слайдер, если его нет (с настройками)
        $this->setSlider(__DIR__ . '/src/slide-billberry.jpg', [
            'scroll' => 'always',
            'bullet' => 'false',
        ], [
            'text1' => <<<TEXT1
<p>Финская черника</p>
TEXT1
        ]);

        $this->setSliderTools([
            'transition' => 'crossfade',
            'autoplay' => '4000',
            'loop' => '1',
            'transitionduration' => '1500',
            'maxHeight' => '1000',
            'arrows' => 'always',
            'nav' => 'false',
            'minHeight1280' => 350,
            'minHeight1024' => 350,
            'minHeight768' => 350,
            'minHeight350' => 350,
        ]);
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

        /*-Верхнее меню-*/

        $this->setCssVal('menu.top.position', 'left');

        $this->setCssVal('menu.top.level1.level1_bg.color', '#84bbff');
        $this->setCssVal('menu.top.level1.level1_bg.padding', '0');

        $this->setCssVal('menu.top.level1.normal.bullit.img', $this->sPathDir . '/menutop.dot.png');
        $this->setCssVal('menu.top.level1.normal.bullit.hvalue', '0');
        $this->setCssVal('menu.top.level1.normal.bullit.vvalue', '0');
        $this->setCssVal('menu.top.level1.normal.bullit.height', '100%');
        $this->setCssVal('menu.top.level1.normal.bullit.width', '30px');
        $this->setCssVal('menu.top.level1.normal.background.color', '#84bbff');
        $this->setCssVal('menu.top.level1.normal.background.padding', '17px 20px 18px 20px');
        $this->setCssVal('menu.top.level1.normal.link.size', '14px');
        $this->setCssVal('menu.top.level1.normal.link.transform', 'none');
        $this->setCssVal('menu.top.level1.normal.link.color', '#fff');

        $this->setCssVal('menu.top.level1.active.bullit.img', $this->sPathDir . '/menutop.dot.on.png');
        $this->setCssVal('menu.top.level1.active.bullit.hvalue', '0');
        $this->setCssVal('menu.top.level1.active.bullit.vvalue', '0');
        $this->setCssVal('menu.top.level1.active.bullit.height', '100%');
        $this->setCssVal('menu.top.level1.active.bullit.width', '30px');
        $this->setCssVal('menu.top.level1.active.background.color', '#6ca7ee');
        $this->setCssVal('menu.top.level1.active.background.padding', '17px 20px 18px 20px');
        $this->setCssVal('menu.top.level1.active.link.size', '14px');
        $this->setCssVal('menu.top.level1.active.link.transform', 'none');
        $this->setCssVal('menu.top.level1.active.link.color', '#fff');

        $this->setCssVal('menu.top.level2.normal.background.color', '#6ca7ee');
        $this->setCssVal('menu.top.level2.normal.background.padding', '14px 20px 14px 20px');
        $this->setCssVal('menu.top.level2.normal.link.size', '14px');
        $this->setCssVal('menu.top.level2.normal.link.transform', 'none');
        $this->setCssVal('menu.top.level2.normal.link.color', '#fff');

        $this->setCssVal('menu.top.level2.active.background.color', '#84bbff');
        $this->setCssVal('menu.top.level2.active.background.padding', '14px 20px 14px 20px');
        $this->setCssVal('menu.top.level2.active.link.size', '14px');
        $this->setCssVal('menu.top.level2.active.link.transform', 'none');
        $this->setCssVal('menu.top.level2.active.link.color', '#fff');

        $this->setCssVal('menu.top.level3.normal.background.color', '#84bbff');
        $this->setCssVal('menu.top.level3.normal.background.padding', '14px 20px 14px 20px');
        $this->setCssVal('menu.top.level3.normal.link.size', '14px');
        $this->setCssVal('menu.top.level3.normal.link.transform', 'none');
        $this->setCssVal('menu.top.level3.normal.link.color', '#fff');

        $this->setCssVal('menu.top.level3.active.background.color', '#6ca7ee');
        $this->setCssVal('menu.top.level3.active.background.padding', '14px 20px 14px 20px');
        $this->setCssVal('menu.top.level3.active.link.size', '14px');
        $this->setCssVal('menu.top.level3.active.link.transform', 'none');
        $this->setCssVal('menu.top.level3.active.link.color', '#fff');

        /*-Левое меню-*/

        $this->setCssVal('menu.left.level1.level1_bg.color', '#4381cd');

        $this->setCssVal('menu.left.level1.normal.bullit.img', $this->sPathDir . '/menutop.dot.png');
        $this->setCssVal('menu.left.level1.normal.bullit.hvalue', '0');
        $this->setCssVal('menu.left.level1.normal.bullit.vvalue', '0');
        $this->setCssVal('menu.left.level1.normal.bullit.height', '100%');
        $this->setCssVal('menu.left.level1.normal.bullit.width', '30px');
        $this->setCssVal('menu.left.level1.normal.background.color', 'transparent');
        $this->setCssVal('menu.left.level1.normal.background.img', '');
        $this->setCssVal('menu.left.level1.normal.background.padding', '25px 20px 25px 20px');
        $this->setCssVal('menu.left.level1.normal.link.transform', 'none');
        $this->setCssVal('menu.left.level1.normal.link.size', '16px');
        $this->setCssVal('menu.left.level1.normal.link.color', '#fff');

        $this->setCssVal('menu.left.level1.active.bullit.img', $this->sPathDir . '/menutop.dot.on.png');
        $this->setCssVal('menu.left.level1.active.bullit.hvalue', '0');
        $this->setCssVal('menu.left.level1.active.bullit.vvalue', '0');
        $this->setCssVal('menu.left.level1.active.bullit.height', '100%');
        $this->setCssVal('menu.left.level1.active.bullit.width', '30px');
        $this->setCssVal('menu.left.level1.active.background.color', '#6ca7ee');
        $this->setCssVal('menu.left.level1.active.background.img', '');
        $this->setCssVal('menu.left.level1.active.background.padding', '25px 20px 25px 20px');
        $this->setCssVal('menu.left.level1.active.link.transform', 'none');
        $this->setCssVal('menu.left.level1.active.link.size', '16px');
        $this->setCssVal('menu.left.level1.active.link.color', '#fff');

        $this->setCssVal('menu.left.level2.normal.background.color', '#6ca7ee');
        $this->setCssVal('menu.left.level2.normal.background.img', '');
        $this->setCssVal('menu.left.level2.normal.background.padding', '10px 20px 10px 20px');
        $this->setCssVal('menu.left.level2.normal.link.transform', 'none');
        $this->setCssVal('menu.left.level2.normal.link.size', '16px');
        $this->setCssVal('menu.left.level2.normal.link.color', '#fff');

        $this->setCssVal('menu.left.level2.active.background.color', '#4381cd');
        $this->setCssVal('menu.left.level2.active.background.img', '');
        $this->setCssVal('menu.left.level2.active.background.padding', '10px 20px 10px 20px');
        $this->setCssVal('menu.left.level2.active.link.transform', 'none');
        $this->setCssVal('menu.left.level2.active.link.size', '16px');
        $this->setCssVal('menu.left.level2.active.link.color', '#fff');

        $this->setCssVal('menu.left.level3.normal.background.color', '#4381cd');
        $this->setCssVal('menu.left.level3.normal.background.img', '');
        $this->setCssVal('menu.left.level3.normal.background.padding', '10px 20px 10px 20px');
        $this->setCssVal('menu.left.level3.normal.link.transform', 'none');
        $this->setCssVal('menu.left.level3.normal.link.size', '14px');
        $this->setCssVal('menu.left.level3.normal.link.color', '#84bbff');

        $this->setCssVal('menu.left.level3.active.background.color', '#6ca7ee');
        $this->setCssVal('menu.left.level3.active.background.img', '');
        $this->setCssVal('menu.left.level3.active.background.padding', '10px 20px 10px 20px');
        $this->setCssVal('menu.left.level3.active.link.transform', 'none');
        $this->setCssVal('menu.left.level3.active.link.size', '16px4');
        $this->setCssVal('menu.left.level3.active.link.color', '#fff');

        /*-Блоки в шапке-*/

        $this->setCssVal('page.head.logo.h_value', '20px');
        $this->setCssVal('page.head.logo.v_value', '70px');

        $this->setCssVal('page.head.pilot1.h_position', 'right');
        $this->setCssVal('page.head.pilot1.h_value', '255px');
        $this->setCssVal('page.head.pilot1.v_value', '85px');
        $this->setCssVal('page.head.pilot1.width', '140px');

        $this->setCssVal('page.head.pilot2.h_position', 'right');
        $this->setCssVal('page.head.pilot2.h_value', '400px');
        $this->setCssVal('page.head.pilot2.v_value', '83px');
        $this->setCssVal('page.head.pilot2.width', '210px');

        $this->setCssVal('page.head.pilot3.h_value', '100%');
        $this->setCssVal('page.head.pilot4.h_value', '100%');
        $this->setCssVal('page.head.pilot5.h_value', '100%');

        /*-Слайдер-*/

        $this->setCssVal('modules.slider.navmargin', '10px');
        $this->setCssVal('modules.slider.height_arrow', '40px');
        $this->setCssVal('modules.slider.width_arrow', '30px');
        $this->setCssVal('modules.slider.back_img', $this->sPathDir . '/slider-prev.png');
        $this->setCssVal('modules.slider.next_img', $this->sPathDir . '/slider-next.png');

        /*-Корзина-*/
        if ($this->moduleExists('MiniCart')) {
            $this->setCssVal('modules.basketmain.padding_l', '90px');
            $this->setCssVal('modules.basketmain.backimg', $this->sPathDir . '/shcart.icon1.png');
            $this->setCssVal('modules.basketmain.backcolor', 'transparent');
            $this->setCssVal('modules.basketmain.bordercolor', 'transparent');
            $this->setCssVal('modules.basketmain.height', '70px');
            $this->setCssVal('modules.basketmain.head.width', '220px');
            $this->setCssVal('modules.basketmain.color', '#4381cd');
            $this->setCssVal('modules.basketmain.head.v_value', '30px');
            $this->setCssVal('modules.basketmain.head.h_position', 'right');
            $this->setCssVal('modules.basketmain.head.h_value', '20px');
        }

        /*-Поиск-*/

        $this->setCssVal('modules.search.btn_img', $this->sPathDir . '/search.icon1.png');
        $this->setCssVal('modules.search.btn_width', '60px');
        $this->setCssVal('modules.search.btn_height', '22px');
        $this->setCssVal('modules.search.head.bgcolor', 'transparent');
        $this->setCssVal('modules.search.head.textcolor', '#000');

        /*-Форма авторизации-*/
        if ($this->moduleExists('Auth')) {
            $this->setCssVal('modules.auth.authmain.bgcolor', 'transparent');
            $this->setCssVal('modules.auth.authmain.bordercolor', 'transparent');
            $this->setCssVal('modules.auth.authmain.marginb', '0');
            $this->setCssVal('modules.forms.padding', '16px 0');
            $this->setCssVal('modules.auth.authmain.backcolor', '#84bbff');
            $this->setCssVal('modules.auth.authmain.backcolorh', '#4381cd');
        }

        if (Adaptive::modeIsActive()) {
            /*-Адаптивный режим-*/

            $this->setCssVal('adaptive.sidebar.icon_close.pic', $this->sPathDir . '/icon-close.png');
            $this->setCssVal('adaptive.sidebar.icon_close.background_color_block', '#84bbff');
            $this->setCssVal('adaptive.sidebar.icon_close.hor_pos', 'left');

            $this->setCssVal('adaptive.sidebar.icon_sandwich.pic', $this->sPathDir . '/menutop.iconbg.gif');

            $this->setCssVal('adaptive.sidebar.params.background_color', '#6ca7ee');

            $this->setCssVal('adaptive.side_menu.separ_color', '#5a96df');
        }
    }

    /**
     * Установить типовой контент
     */
    public function setContent()
    {
        $this->copyDirFiles('web/images', $this->sPathDir);
        $this->copyDirFiles('src', $this->sPathDir);

        $this->setLogo($this->sPathDir . '/logo.gif');

        $iCallbackSection = \Yii::$app->sections->getValue('callback');

        $this->setBlockText(Block::pilot1, '<div class="b-head-time">Время работы: <br> пн-пт: 9:00 - 21:00 <br> сб-вс: выходной</div>');
        $this->setBlockText(Block::pilot2, '<div class="b-head-phone"><a href="tel:+79000000000">+7 (900) 000 00 00</a></div><div class="b-head-callback"><a class="js-callback" data-ajaxform="1" data-section="' . $iCallbackSection . '" data-js_max_width="600" data-width-type="px" href="#">Заказать обратный звонок</a></div>');
        $this->setBlockText(Block::pilot3, '');
        $this->setBlockText(Block::pilot4, '');
        $this->setBlockText(Block::pilot5, '');
    }
}
