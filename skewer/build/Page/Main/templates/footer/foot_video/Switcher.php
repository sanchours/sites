<?php

namespace skewer\build\Page\Main\templates\footer\foot_video;

use skewer\components\design\TplSwitchFooter;

class Switcher extends TplSwitchFooter
{
    public $sPathDir = '/files/foot_video/images';

    public $bUse = true;

    /**
     * Отдает имя название шаблона.
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Подвал Видео';
    }

    /**
     * Задать набор настроек для модулей.
     */
    public function setModuleSettings()
    {
    }

    /**
     * Задать настройки для типовых блоков.
     */
    public function setBlocks()
    {
        /*-Настройки-*/

        $this->setCssVal('page.footer.height', '642px');
        $this->setCssVal('page.footer.size', '14px');
        $this->setCssVal('page.footer.color', '#999999');
        $this->setCssVal('page.footer.image', $this->sPathDir . '/fon-footer.jpg');
        $this->setCssVal('page.footer.position_h', 'center');
        $this->setCssVal('page.footer.footerboxl.color', 'transparent');
        $this->setCssVal('page.footer.footerboxr.color', 'transparent');
        $this->setCssVal('page.footer.footerboxr.image', 'empty');
        $this->setCssVal('page.footer.footerboxl.image', 'empty');
        $this->setCssVal('page.footer.color_t', '#999999');
        $this->setCssVal('page.footer.color_a', '#fff');

        /*-Блоки в подвале-*/
        $this->setCssVal('page.footer.grid1.h_position', 'left');
        $this->setCssVal('page.footer.grid1.h_value', '30px');
        $this->setCssVal('page.footer.grid1.v_value', '242px');
        $this->setCssVal('page.footer.grid1.width', '410px');

        $this->setCssVal('page.footer.grid2.h_value', '-1000px');

        $this->setCssVal('page.footer.grid3.h_position', 'left');
        $this->setCssVal('page.footer.grid3.h_value', '30px');
        $this->setCssVal('page.footer.grid3.v_value', '191px');
        $this->setCssVal('page.footer.grid3.width', '250px');

        $this->setCssVal('page.footer.grid4.h_position', 'left');
        $this->setCssVal('page.footer.grid4.h_value', '220px');
        $this->setCssVal('page.footer.grid4.v_value', '77px');
        $this->setCssVal('page.footer.grid4.width', '400px');

        $this->setCssVal('page.footer.grid5.h_position', 'left');
        $this->setCssVal('page.footer.grid5.h_value', '30px');
        $this->setCssVal('page.footer.grid5.v_value', '70px');
        $this->setCssVal('page.footer.grid5.width', '370px');

        $this->setCssVal('page.footer.grid7.h_position', 'left');
        $this->setCssVal('page.footer.grid7.h_value', '30px');
        $this->setCssVal('page.footer.grid7.v_value', '568px');
        $this->setCssVal('page.footer.grid7.width', '285px');
    }

    /**
     * Установить типовой контент
     */
    public function setContent()
    {
        $this->copyDirFiles('web/images', $this->sPathDir);

        $this->setBlockText(
            'contacts',
            <<<TEXT1
        <div class="b-foot-social">
            <a href="#"><img alt="" src="{$this->sPathDir}/vk-icon.png" /></a>
            <a href="#"><img alt="" src="{$this->sPathDir}/fb-icon.png" /></a>
            <a href="#"><img alt="" src="{$this->sPathDir}/tw-icon.png" /></a> 
        </div>
        <div class="b-foot-contacts">
        <div class="b-foot-phone"><a href="tel:79005555005">+7 900 555 50 05</a>
        </div>
        <p>Москва, пр. Виторио Доннарумы, 12</p>
        <p><a href="mailto:videouniform@gmail.com">videouniform@gmail.com</a></p>
        </div>

TEXT1
        );

        $this->setBlockText(
            'footertext4',
            <<<TEXT2
            <div class="b-foot-menu">
                <div class="foot-menu__item">
                    <ul>
                        <li><a href="#">фото</a></li>
                        <li><a href="#">видео</a></li> 
                    </ul>
                </div>
                <div class="foot-menu__item">
                    <ul>
                        <li><a href="#">портфолио</a></li>
                        <li><a href="#">о студии</a></li> 
                    </ul>
                </div>

                <div class="foot-menu__item">
                <ul>
                	<li><a href="#">Отзывы</a></li>
                	<li><a href="#">Контакты</a></li> 
                </ul>
                </div>
            </div>
TEXT2
);

        $this->setBlockText(
            'footertext5',
            <<<TEXT3
    <div class="b-footer-logo"><img alt="logofooter" src="{$this->sPathDir}/logo-footer.png" /></div>

TEXT3
);

        // $this->setBlockText('footertext5', '');
        $this->setBlockText('counters', '<span class="counter__item"><img alt="" src="' . $this->sPathDir . '/counter_blank.gif" width="88" height="31" /></span>');
        $this->setBlockText('copyright', '<p>&copy; BC group – профессиональная видеосъемка, [Year]</p>');
        $this->setBlockText('copyright_dev', '<div class="b-foot-copy-dev">
            <div class="foot-copy-dev__link">
                <a href="https://www.web-canape.ru/razrabotka-sajta/?utm_source=copyright">Разработка</a> и <a href="https://www.web-canape.ru/prodvizhenie-sajtov/?utm_source=copyright">маркетинг</a> - WebCanape
            </div>
    </div>');
    }
}
