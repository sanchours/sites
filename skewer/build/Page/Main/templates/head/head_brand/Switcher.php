<?php

namespace skewer\build\Page\Main\templates\head\head_brand;

use skewer\build\Design\Zones;
use skewer\components\design\Block;
use skewer\components\design\TplSwitchHead;
use skewer\helpers\Adaptive;

class Switcher extends TplSwitchHead
{
    public $bUse = false;

    public $sPathDir = '/files/head_brand/images';

    /**
     * Отдает имя название шаблона.
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Бренды';
    }

    /**
     * Отдает набор меток модулей, которые должны быть выведены в шапку.
     */
    protected function getModulesList()
    {
        return 'authHead,searchHead,AdaptiveMode,topMenu,minicartHead,emptyHeadBlock';
    }

    /**
     * Задать набор настроек для модулей.
     */
    public function setModuleSettings()
    {
//        $this->setModulesToLayout('adaptive_menu', 'authHead,minicartHead,headtext1,adaptive_menu_topMenu,adaptive_menu_leftMenu');

        $this->setModulesToLayout('fixed_menu', 'AdaptiveMode,topMenu,headtext1,minicartHead');

        // Переопределение лейаута для главной
        $this->setParam(
            \Yii::$app->sections->main(),
            Zones\Api::layoutGroupName,
            'head',
            'authHead,searchHead,AdaptiveMode,topMenu,minicartHead,mainBanner'
        );

        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'topMenu',
            'templateFile',
            'topMenuDropdown.twig'
        );

        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'minicartHead',
            'template',
            'head-brand.twig'
        );

        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'authHead',
            'miniAuthHeadTpl',
            'AuthFormMiniHeadIcon.twig'
        );

        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'AdaptiveMode',
            'headTpl',
            'head_brand.php'
        );

        $this->setParam(
            \Yii::$app->sections->tplNew(),
            'AdaptiveMode',
            'contentTpl',
            'content_brand.php'
        );

        // создаем слайдер
        $this->setSlider(__DIR__ . '/web/src/head-brand-slide.jpg', [
            'scroll' => 'always',
            'bullet' => 'false',
        ], [
            'text1' => <<<TEXT1
<div class="b-picture-text">
    <h2>Брендовые вещи из Европы<br>для всей семьи</h2>
</div>
TEXT1
        , 'text2' => <<<TEXT2
<div class="b-head-brands hide-on-mobile"><div class="head-brands__inner"><div class="head-brands__item"><a href="#"><img src="{$this->sPathDir}/brand-1.png" alt=""></a></div><div class="head-brands__item"><a href="#"><img src="{$this->sPathDir}/brand-2.png" alt=""></a></div><div class="head-brands__item"><a href="#"><img src="{$this->sPathDir}/brand-3.png" alt=""></a></div><div class="head-brands__item"><a href="#"><img src="{$this->sPathDir}/brand-4.png" alt=""></a></div><div class="head-brands__item"><a href="#"><img src="{$this->sPathDir}/brand-5.png" alt=""></a></div><div class="head-brands__item"><a href="#"><img src="{$this->sPathDir}/brand-6.png" alt=""></a></div><div class="head-brands__item"><a href="#"><img src="{$this->sPathDir}/brand-7.png" alt=""></a></div></div></div>
TEXT2
        ]);

        // задаем настройки слайдеру
        $this->setSliderTools([
            'transition' => 'slide',
            'autoplay' => '4000',
            'loop' => '1',
            'transitionduration' => '1500',
            'maxHeight' => '1000',
            'minHeight1280' => '740',
            'minHeight1024' => '700',
            'minHeight768' => '600',
            'minHeight350' => '450',
        ]);
    }

    /**
     * Задать настройки для типовых блоков.
     */
    public function setBlocks()
    {
        /*-Логотип-*/
        $this->setCssVal('page.head.logo.h_position', 'left');
        $this->setCssVal('page.head.logo.h_value', '50%');
        $this->setCssVal('page.head.logo.v_value', '56px');

        /*-Шапка сайта-*/
        $this->setCssVal('page.head.img.image', $this->sPathDir . '/head-brand-slide.jpg');
        $this->setCssVal('page.head.img.color', '#223138');
        $this->setCssVal('page.head.img.height', '225px');
        $this->setCssVal('page.head.img.position_h', 'center');
        $this->setCssVal('page.head.img.position_v', '80%');

        /*-Блоки в шапке-*/
        $this->setCssVal('page.head.pilot1.h_position', 'left');
        $this->setCssVal('page.head.pilot1.h_value', '110px');
        $this->setCssVal('page.head.pilot1.v_value', '70px');
        $this->setCssVal('page.head.pilot1.width', '200px');

        $this->setCssVal('page.head.pilot2.h_value', '100%');
        $this->setCssVal('page.head.pilot3.h_value', '100%');
        $this->setCssVal('page.head.pilot4.h_value', '100%');
        $this->setCssVal('page.head.pilot5.h_value', '100%');

        $this->setCssVal('page.head.color_a', '#fff');

        /*-Верхнее меню-*/
        $this->setCssVal('menu.top.position', 'center');

        $this->setCssVal('menu.top.level1.level1_bg.color', 'transparent');
        $this->setCssVal('menu.top.level1.level1_bg.padding', '0');

        $this->setCssVal('menu.top.level1.normal.bullit.img', $this->sPathDir . '/menutop-mar.png');
        $this->setCssVal('menu.top.level1.normal.bullit.hvalue', '0');
        $this->setCssVal('menu.top.level1.normal.bullit.vvalue', '0');
        $this->setCssVal('menu.top.level1.normal.bullit.height', '100%');
        $this->setCssVal('menu.top.level1.normal.bullit.width', '30px');
        $this->setCssVal('menu.top.level1.normal.background.color', 'transparent');
        $this->setCssVal('menu.top.level1.normal.background.padding', '15px 20px');
        $this->setCssVal('menu.top.level1.normal.link.size', '14px');
        $this->setCssVal('menu.top.level1.normal.link.transform', 'none');
        $this->setCssVal('menu.top.level1.normal.link.color', '#fff');

        $this->setCssVal('menu.top.level1.active.bullit.img', $this->sPathDir . '/menutop-mar-on.png');
        $this->setCssVal('menu.top.level1.active.bullit.hvalue', '0');
        $this->setCssVal('menu.top.level1.active.bullit.vvalue', '0');
        $this->setCssVal('menu.top.level1.active.bullit.height', '100%');
        $this->setCssVal('menu.top.level1.active.bullit.width', '30px');
        $this->setCssVal('menu.top.level1.active.background.color', 'transparent');
        $this->setCssVal('menu.top.level1.active.background.padding', '15px 20px');
        $this->setCssVal('menu.top.level1.active.link.size', '14px');
        $this->setCssVal('menu.top.level1.active.link.transform', 'none');
        $this->setCssVal('menu.top.level1.active.link.color', '#3dade1');

        $this->setCssVal('menu.top.level2.width', '200px');

        $this->setCssVal('menu.top.level2.normal.background.color', '#333');
        $this->setCssVal('menu.top.level2.normal.background.padding', '10px 30px');
        $this->setCssVal('menu.top.level2.normal.link.size', '16px');
        $this->setCssVal('menu.top.level2.normal.link.transform', 'none');
        $this->setCssVal('menu.top.level2.normal.link.color', '#bbb');
        $this->setCssVal('menu.top.level2.normal.bullit.width', '30px');
        $this->setCssVal('menu.top.level2.normal.bullit.height', '30px');
        $this->setCssVal('menu.top.level2.normal.bullit.vvalue', '16px');

        $this->setCssVal('menu.top.level2.active.background.color', '#333');
        $this->setCssVal('menu.top.level2.active.background.padding', '10px 30px');
        $this->setCssVal('menu.top.level2.active.link.size', '16px');
        $this->setCssVal('menu.top.level2.active.link.transform', 'none');
        $this->setCssVal('menu.top.level2.active.link.color', '#fff');
        $this->setCssVal('menu.top.level2.active.bullit.width', '30px');
        $this->setCssVal('menu.top.level2.active.bullit.height', '30px');
        $this->setCssVal('menu.top.level2.active.bullit.vvalue', '16px');

        $this->setCssVal('menu.top.level3.width', '200px');

        $this->setCssVal('menu.top.level3.normal.background.color', '#222');
        $this->setCssVal('menu.top.level3.normal.background.padding', '10px 30px');
        $this->setCssVal('menu.top.level3.normal.link.size', '14px');
        $this->setCssVal('menu.top.level3.normal.link.transform', 'none');
        $this->setCssVal('menu.top.level3.normal.link.color', '#bbb');

        $this->setCssVal('menu.top.level3.active.background.color', '#222');
        $this->setCssVal('menu.top.level3.active.background.padding', '10px 30px');
        $this->setCssVal('menu.top.level3.active.link.size', '14px');
        $this->setCssVal('menu.top.level3.active.link.transform', 'none');
        $this->setCssVal('menu.top.level3.active.link.color', '#fff');

        /*-Слайдер-*/
        $this->setCssVal('modules.slider.height_arrow', '60px');
        $this->setCssVal('modules.slider.width_arrow', '30px');
        $this->setCssVal('modules.slider.back_img', $this->sPathDir . '/slide-prev.png');
        $this->setCssVal('modules.slider.next_img', $this->sPathDir . '/slide-next.png');
        $this->setCssVal('modules.slider.navmargin', '50px');

        /*-Авторизация-*/
        $this->setCssVal('modules.basketmain.head.v_value', '73px');
        $this->setCssVal('modules.basketmain.head.h_position', 'right');
        $this->setCssVal('modules.basketmain.head.h_value', '30px');
        $this->setCssVal('modules.basketmain.backimg', $this->sPathDir . '/basket-icon.png');
        $this->setCssVal('modules.basketmain.backcolor', 'transparent');
        $this->setCssVal('modules.basketmain.bordercolor', 'transparent');
        $this->setCssVal('modules.basketmain.backhposition', '50%');
        $this->setCssVal('modules.basketmain.backvposition', '50%');
        $this->setCssVal('modules.basketmain.height', '30px');
        $this->setCssVal('modules.basketmain.head.width', '30px');

        if (Adaptive::modeIsActive()) {
            /*-Адаптивный режим-*/

            $this->setCssVal('adaptive.sidebar.icon_close.pic', $this->sPathDir . '/sidebar-close.png');
            $this->setCssVal('adaptive.sidebar.icon_close.background_color_block', '#1e1e1e');
            $this->setCssVal('adaptive.sidebar.icon_close.hor_pos', 'left');
            $this->setCssVal('adaptive.sidebar.icon_close.height', '60px');

            $this->setCssVal('adaptive.sidebar.icon_sandwich.pic', $this->sPathDir . '/icon-sandwich.png');
            $this->setCssVal('adaptive.sidebar.icon_sandwich.width', '30px');
            $this->setCssVal('adaptive.sidebar.icon_sandwich.height', '40px');

            $this->setCssVal('adaptive.sidebar.params.background_color', '#1e1e1e');

            $this->setCssVal('adaptive.side_menu.separ_color', '#1e1e1e');
            $this->setCssVal('adaptive.side_menu.mar_open', $this->sPathDir . '/menutop-mar.png');
            $this->setCssVal('adaptive.side_menu.mar_close', $this->sPathDir . '/menutop-mar-on.png');
        }
    }

    /**
     * Установить типовой контент
     */
    public function setContent()
    {
        $this->copyDirFiles('web/images', $this->sPathDir);
        $this->copyDirFiles('src', $this->sPathDir);
        $this->setLogo($this->sPathDir . '/logo-brands.png');

        $this->setBlockText(Block::pilot1, '<div class="b-head-phone"><a href="tel:+79005555005">+7 900 555 50 05</a></div><div class="b-head-email"><a href="mailto:fashionuniform@gmail.com">fashionuniform@gmail.com</a></div>');
        $this->setBlockText(Block::pilot2, '');
        $this->setBlockText(Block::pilot3, '');
        $this->setBlockText(Block::pilot4, '');
        $this->setBlockText(Block::pilot5, '');
    }
}
