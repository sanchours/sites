<?php

namespace skewer\build\Page\Main\templates\head\head_watermelon;

use skewer\build\Design\Zones;
use skewer\components\design\Block;
use skewer\components\design\Design;
use skewer\components\design\TplSwitchHead;
use skewer\helpers\Adaptive;

class Switcher extends TplSwitchHead
{
    public $sPathDir = '/files/head_watermelon/images';

    /**
     * Отдает имя название шаблона.
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Арбузы';
    }

    /**
     * Отдает набор меток модулей, которые должны быть выведены в шапку.
     */
    protected function getModulesList()
    {
        $sAdapt = Design::modeIsActive() ? 'AdaptiveMode' : '';

        return "{$sAdapt},topMenu,emptyHeadBlock,mainBanner";
    }

    /**
     * Задать набор настроек для модулей.
     */
    public function setModuleSettings()
    {
        // Переопределение лейаута для сайдбара
        $this->setModulesToLayout('adaptive_menu', 'headtext1,adaptive_menu_topMenu');

        $sAdapt = Design::modeIsActive() ? 'AdaptiveMode' : '';

        // Переопределение лейаута для главной
        $this->setParam(
            \Yii::$app->sections->main(),
            Zones\Api::layoutGroupName,
            'head',
            "{$sAdapt},topMenu,mainBanner"
        );

        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'topMenu',
            'templateFile',
            'topMenuWatermelon.twig'
        );

        // создать слайдер, если его нет (с настройками)
        $this->setSlider(__DIR__ . '/src/watermellon.jpg', [
            'scroll' => 'always',
            'bullet' => 'dots',
        ], [
            'text1' => <<<TEXT1
<h2 style="text-align: center;">Астраханские&nbsp;<br />арбузы</h2>
<div class="hide-on-mobile">
    <p id="ext-gen3494" style="text-align: center;">Арбузный фестиваль ежегодно проходит в небольшом&nbsp;<br />городке Чинчилла, Квинсленд, Канада</p>
</div>
<div class="picture__btn"><a href="#">Хочу арбуз!</a></div>
TEXT1
        ]);

        $this->setSliderTools([
            'transition' => 'crossfade',
            'autoplay' => '4000',
            'loop' => '1',
            'transitionduration' => '1500',
            'maxHeight' => '1000',
            'minHeight1280' => '',
            'minHeight1024' => '',
            'minHeight768' => 550,
            'minHeight350' => 550,
        ]);
    }

    /**
     * Задать настройки для типовых блоков.
     */
    public function setBlocks()
    {
        /*-Верхнее меню-*/

        $this->setCssVal('menu.top.position', 'center');

        $this->setCssVal('menu.top.level1.level1_bg.color', 'transparent');
        $this->setCssVal('menu.top.level1.level1_bg.padding', '40px 0 0 0');

        $this->setCssVal('menu.top.level1.normal.bullit.img', $this->sPathDir . '/menu-top-icon.png');
        $this->setCssVal('menu.top.level1.normal.bullit.hvalue', '0');
        $this->setCssVal('menu.top.level1.normal.bullit.vvalue', '0');
        $this->setCssVal('menu.top.level1.normal.bullit.height', '100%');
        $this->setCssVal('menu.top.level1.normal.bullit.width', '30px');
        $this->setCssVal('menu.top.level1.normal.background.color', 'transparent');
        $this->setCssVal('menu.top.level1.normal.background.padding', '15px 20px 15px 20px');
        $this->setCssVal('menu.top.level1.normal.link.size', '16px');
        $this->setCssVal('menu.top.level1.normal.link.transform', 'none');
        $this->setCssVal('menu.top.level1.normal.link.color', '#fff');

        $this->setCssVal('menu.top.level1.active.bullit.img', $this->sPathDir . '/menu-top-icon-on.png');
        $this->setCssVal('menu.top.level1.active.bullit.hvalue', '0');
        $this->setCssVal('menu.top.level1.active.bullit.vvalue', '0');
        $this->setCssVal('menu.top.level1.active.bullit.height', '100%');
        $this->setCssVal('menu.top.level1.active.bullit.width', '30px');
        $this->setCssVal('menu.top.level1.active.background.color', 'transparent');
        $this->setCssVal('menu.top.level1.active.background.padding', '15px 20px 15px 20px');
        $this->setCssVal('menu.top.level1.active.link.size', '16px');
        $this->setCssVal('menu.top.level1.active.link.transform', 'none');
        $this->setCssVal('menu.top.level1.active.link.color', '#64d34a');

        $this->setCssVal('menu.top.level2.width', '210px');

        $this->setCssVal('menu.top.level2.normal.background.color', 'transparent');
        $this->setCssVal('menu.top.level2.normal.background.padding', '15px 20px 15px 20px');
        $this->setCssVal('menu.top.level2.normal.link.size', '14px');
        $this->setCssVal('menu.top.level2.normal.link.transform', 'none');
        $this->setCssVal('menu.top.level2.normal.link.color', '#fff');

        $this->setCssVal('menu.top.level2.active.background.color', 'transparent');
        $this->setCssVal('menu.top.level2.active.background.padding', '15px 20px 15px 20px');
        $this->setCssVal('menu.top.level2.active.link.size', '14px');
        $this->setCssVal('menu.top.level2.active.link.transform', 'none');
        $this->setCssVal('menu.top.level2.active.link.color', '#64d34a');

        $this->setCssVal('menu.top.level3.width', '210px');

        $this->setCssVal('menu.top.level3.normal.background.color', 'transparent');
        $this->setCssVal('menu.top.level3.normal.background.padding', '15px 20px 15px 20px');
        $this->setCssVal('menu.top.level3.normal.link.size', '12px');
        $this->setCssVal('menu.top.level3.normal.link.transform', 'none');
        $this->setCssVal('menu.top.level3.normal.link.color', '#64d34a');

        $this->setCssVal('menu.top.level3.active.background.color', 'transparent');
        $this->setCssVal('menu.top.level3.active.background.padding', '15px 20px 15px 20px');
        $this->setCssVal('menu.top.level3.active.link.size', '12px');
        $this->setCssVal('menu.top.level3.active.link.transform', 'none');
        $this->setCssVal('menu.top.level3.active.link.color', '#fff');

        /*-Слайдер-*/

        $this->setCssVal('modules.slider.width_bul', '10px');
        $this->setCssVal('modules.slider.height_bul', '10px');
        $this->setCssVal('modules.slider.bul_bgimg', $this->sPathDir . '/slide-mar.png');
        $this->setCssVal('modules.slider.bulon_bgimg', $this->sPathDir . '/slide-mar-on.png');
        $this->setCssVal('modules.slider.height_arrow', '55px');
        $this->setCssVal('modules.slider.width_arrow', '55px');
        $this->setCssVal('modules.slider.back_img', $this->sPathDir . '/slide-prev.png');
        $this->setCssVal('modules.slider.next_img', $this->sPathDir . '/slide-next.png');

        /*-Шапка сайта-*/

        $this->copyDirFiles('web/images', $this->sPathDir);
        $this->copyDirFiles('src', $this->sPathDir);

        $this->setLogo($this->sPathDir . '/watermellon.head.jpg');

        $this->setCssVal('page.head.img.image', $this->sPathDir . '/watermellon.jpg');
        $this->setCssVal('page.head.img.height', '120px');

        /*-Блоки в шапке-*/

        $this->setCssVal('page.head.color_a', '#64d34a');
        $this->setCssVal('editor.a.color', '#64d34a');

        $this->setCssVal('page.head.logo.h_value', '20px');
        $this->setCssVal('page.head.logo.v_value', '30px');

        $this->setCssVal('page.head.pilot1.h_position', 'right');
        $this->setCssVal('page.head.pilot1.h_value', '20px');
        $this->setCssVal('page.head.pilot1.v_value', '30px');
        $this->setCssVal('page.head.pilot1.width', '240px');

        $this->setCssVal('page.head.pilot2.h_value', '100%');
        $this->setCssVal('page.head.pilot3.h_value', '100%');
        $this->setCssVal('page.head.pilot4.h_value', '100%');
        $this->setCssVal('page.head.pilot5.h_value', '100%');

        /*-Адптивный режим-*/

        if (Adaptive::modeIsActive()) {
            $this->setCssVal('adaptive.sidebar.icon_sandwich.pic', $this->sPathDir . '/icon-sandwich.png');
            $this->setCssVal('adaptive.sidebar.icon_sandwich.height', '60px');

            $this->setCssVal('adaptive.sidebar.icon_close.pic', $this->sPathDir . '/icon-sandwich-close.png');
            $this->setCssVal('adaptive.sidebar.icon_close.background_color_block', 'transparent');
            $this->setCssVal('adaptive.sidebar.icon_close.hor_pos', 'right');

            $this->setCssVal('adaptive.sidebar.params.background_color_layout', 'rgba(0, 0, 0, .6)');
            $this->setCssVal('adaptive.sidebar.params.background_color', 'rgba(0, 0, 0, .6)');
            $this->setCssVal('adaptive.sidebar.params.link_color', '#64d34a');

            $this->setCssVal('adaptive.side_menu.separ_color', '#64d34a');
            $this->setCssVal('adaptive.side_menu.mar_open', $this->sPathDir . '/menu-top-icon.png');
            $this->setCssVal('adaptive.side_menu.mar_close', $this->sPathDir . '/menu-top-icon-on.png');
        }
    }

    /**
     * Установить типовой контент
     */
    public function setContent()
    {
        $this->copyDirFiles('web/images', $this->sPathDir);
        $this->copyDirFiles('src', $this->sPathDir);

        $this->setLogo($this->sPathDir . '/site-logo.png');

        $this->setBlockText(Block::pilot1, '<div class="b-head-phone"><a href="tel:+79000000000">+7 (900) 000 00 00</a></div><div class="b-head-email"><a href="mailto:namecompany@gmail.com">namecompany@gmail.com</a></div>');
        $this->setBlockText(Block::pilot2, '');
        $this->setBlockText(Block::pilot3, '');
        $this->setBlockText(Block::pilot4, '');
        $this->setBlockText(Block::pilot5, '');
    }
}
