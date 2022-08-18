<?php

namespace skewer\build\Page\Main\templates\head\head_orange;

use skewer\components\design\Block;
use skewer\components\design\Design;
use skewer\components\design\TplSwitchHead;
use skewer\helpers\Adaptive;

class Switcher extends TplSwitchHead
{
    public $bUse = true;

    public $sPathDir = '/files/head_orange/images';

    /**
     * Отдает имя название шаблона.
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Апельсины';
    }

    /**
     * Отдает набор меток модулей, которые должны быть выведены в шапку.
     */
    protected function getModulesList()
    {
        $sAdapt = Design::modeIsActive() ? 'AdaptiveMode' : '';

        return "searchHead,emptyHeadBlock,{$sAdapt},topMenu,mainBanner";
    }

    /**
     * Задать набор настроек для модулей.
     */
    public function setModuleSettings()
    {
        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'topMenu',
            'templateFile',
            'topMenuDropdown.twig'
        );

        // создать слайдер, если его нет (с настройками)
        $this->setSlider(__DIR__ . '/src/slide-orange.jpg', [
            'scroll' => 'always',
            'bullet' => 'dots',
        ], [
            'text1' => <<<TEXT1
<div class="b-picture-text">Марокканские<br />
апельсины</div>
TEXT1
        ]);

        $this->setSliderTools([
            'transition' => 'crossfade',
            'autoplay' => '4000',
            'loop' => '1',
            'transitionduration' => '1450',
            'maxHeight' => '1000',
            'minHeight1280' => '',
            'minHeight1024' => 310,
            'minHeight768' => 310,
            'minHeight350' => 310,
        ]);
    }

    /**
     * Задать настройки для типовых блоков.
     */
    public function setBlocks()
    {
        /*-Шапка-*/

        $this->setCssVal('page.head.img.height', '50px');
        $this->setCssVal('page.head.img.color', '#ececec');
        $this->setCssVal('page.head.color_a', '#888');

        /*-Блоки в шапке-*/

        $this->setCssVal('page.head.logo.h_value', '20px');
        $this->setCssVal('page.head.logo.v_value', '80px');

        $this->setCssVal('page.head.pilot1.h_position', 'left');
        $this->setCssVal('page.head.pilot1.h_value', '20px');
        $this->setCssVal('page.head.pilot1.v_value', '12px');
        $this->setCssVal('page.head.pilot1.width', '550px');

        $this->setCssVal('page.head.pilot2.h_position', 'right');
        $this->setCssVal('page.head.pilot2.h_value', '20px');
        $this->setCssVal('page.head.pilot2.v_value', '80px');
        $this->setCssVal('page.head.pilot2.width', '220px');

        $this->setCssVal('page.head.pilot3.h_value', '100%');
        $this->setCssVal('page.head.pilot4.h_value', '100%');
        $this->setCssVal('page.head.pilot5.h_value', '100%');

        /*-Верхнее меню-*/

        $this->setCssVal('menu.top.position', 'center');
        $this->setCssVal('menu.top.level1.level1_bg.color', '#fff');
        $this->setCssVal('menu.top.level1.level1_bg.padding', '30px 0');

        $this->setCssVal('menu.top.level1.normal.bullit.img', $this->sPathDir . '/menutop-mar.png');
        $this->setCssVal('menu.top.level1.normal.bullit.hvalue', '0');
        $this->setCssVal('menu.top.level1.normal.bullit.vvalue', '0');
        $this->setCssVal('menu.top.level1.normal.bullit.height', '100%');
        $this->setCssVal('menu.top.level1.normal.bullit.width', '30px');
        $this->setCssVal('menu.top.level1.normal.background.color', 'transparent');
        $this->setCssVal('menu.top.level1.normal.background.padding', '17px 20px 18px 20px');
        $this->setCssVal('menu.top.level1.normal.link.size', '16px');
        $this->setCssVal('menu.top.level1.normal.link.transform', 'none');
        $this->setCssVal('menu.top.level1.normal.link.color', '#444');

        $this->setCssVal('menu.top.level1.active.bullit.img', $this->sPathDir . '/menutop-mar-on.png');
        $this->setCssVal('menu.top.level1.active.bullit.hvalue', '0');
        $this->setCssVal('menu.top.level1.active.bullit.vvalue', '0');
        $this->setCssVal('menu.top.level1.active.bullit.height', '100%');
        $this->setCssVal('menu.top.level1.active.bullit.width', '30px');
        $this->setCssVal('menu.top.level1.active.background.color', '#ececec');
        $this->setCssVal('menu.top.level1.active.background.padding', '17px 20px 18px 20px');
        $this->setCssVal('menu.top.level1.active.link.size', '16px');
        $this->setCssVal('menu.top.level1.active.link.transform', 'none');
        $this->setCssVal('menu.top.level1.active.link.color', '#888');

        $this->setCssVal('menu.top.level2.width', '210px');
        $this->setCssVal('menu.top.level2.normal.background.color', '#ececec');
        $this->setCssVal('menu.top.level2.normal.background.padding', '15px 25px');
        $this->setCssVal('menu.top.level2.normal.link.size', '16px');
        $this->setCssVal('menu.top.level2.normal.link.transform', 'none');
        $this->setCssVal('menu.top.level2.normal.link.color', '#444');

        $this->setCssVal('menu.top.level2.active.background.color', '#dfdfdf');
        $this->setCssVal('menu.top.level2.active.background.padding', '15px 25px');
        $this->setCssVal('menu.top.level2.active.link.size', '16px');
        $this->setCssVal('menu.top.level2.active.link.transform', 'none');
        $this->setCssVal('menu.top.level2.active.link.color', '#888');

        $this->setCssVal('menu.top.level3.width', '210px');
        $this->setCssVal('menu.top.level3.normal.background.color', '#dfdfdf');
        $this->setCssVal('menu.top.level3.normal.background.padding', '15px 25px');
        $this->setCssVal('menu.top.level3.normal.link.size', '14px');
        $this->setCssVal('menu.top.level3.normal.link.transform', 'none');
        $this->setCssVal('menu.top.level3.normal.link.color', '#888');

        $this->setCssVal('menu.top.level3.active.background.color', '#dfdfdf');
        $this->setCssVal('menu.top.level3.active.background.padding', '15px 25px');
        $this->setCssVal('menu.top.level3.active.link.size', '14px');
        $this->setCssVal('menu.top.level3.active.link.transform', 'none');
        $this->setCssVal('menu.top.level3.active.link.color', '#444');

        /*-Поиск-*/

//        $this->setCssVal( 'modules.search.btn_img', '20px' );
        $this->setCssVal('modules.search.btn_width', '60px');
        $this->setCssVal('modules.search.head.bgcolor', 'transparent');
        $this->setCssVal('modules.search.head.textcolor', '#000');

        /*-Слайдер-*/

        $this->setCssVal('modules.slider.width_bul', '20px');
        $this->setCssVal('modules.slider.height_bul', '20px');
        $this->setCssVal('modules.slider.bul_bgimg', $this->sPathDir . '/slide-mar.png');
        $this->setCssVal('modules.slider.bulon_bgimg', $this->sPathDir . '/slide-mar-on.png');
        $this->setCssVal('modules.slider.height_arrow', '40px');
        $this->setCssVal('modules.slider.width_arrow', '18px');
        $this->setCssVal('modules.slider.back_img', $this->sPathDir . '/slide-prev.png');
        $this->setCssVal('modules.slider.next_img', $this->sPathDir . '/slide-next.png');
        $this->setCssVal('modules.slider.navmargin', '20px');
        $this->setCssVal('modules.slider.bulletleft', '20%');
        $this->setCssVal('modules.slider.bulletright', '20%');

        if (Adaptive::modeIsActive()) {
            /*-Адаптивный режим-*/

            $this->setCssVal('adaptive.sidebar.icon_close.pic', $this->sPathDir . '/icon-sandwich-close.png');
            $this->setCssVal('adaptive.sidebar.icon_close.background_color_block', '#fff');

            $this->setCssVal('adaptive.sidebar.icon_sandwich.pic', $this->sPathDir . '/icon-sandwich.png');
            $this->setCssVal('adaptive.sidebar.icon_sandwich.height', '110px');

            $this->setCssVal('adaptive.sidebar.params.background_color', '#fff');
            $this->setCssVal('adaptive.sidebar.params.link_color', '#888');

            $this->setCssVal('adaptive.side_menu.separ_color', '#fff');
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
        $this->copyDirFiles('src', $this->sPathDir);

        $this->setLogo($this->sPathDir . '/logo-orange.png');

        $iCallbackSection = \Yii::$app->sections->getValue('callback');

        $this->setBlockText(Block::pilot1, '<div class="b-head-phone"><a href="tel:+79000000000">+7 (900) 000 00 00</a></div><div class="b-head-callback"><a class="js-callback" data-ajaxform="1" data-section="' . $iCallbackSection . '" data-js_max_width="600" data-width-type="px" href="#">Заказать обратный звонок</a></div><div class="b-head-email"><a href="mailto:namecompany@gmail.com">namecompany@gmail.com</a></div>');
        $this->setBlockText(Block::pilot2, '<div class="b-head-btn"><a href="#">Оформить заказ</a></div>');
        $this->setBlockText(Block::pilot3, '');
        $this->setBlockText(Block::pilot4, '');
        $this->setBlockText(Block::pilot5, '');
    }
}
