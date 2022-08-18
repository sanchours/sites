<?php

namespace skewer\build\Page\Main\templates\footer\foot_arconic;

use skewer\components\design\TplSwitchFooter;

class Switcher extends TplSwitchFooter
{
    public $sPathDir = '/files/foot_arconic/images';

    public $bUse = true;

    /**
     * Отдает имя название шаблона.
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Подвал Арконик';
    }

    /**
     * Задать набор настроек для модулей.
     */
    public function setModuleSettings()
    {
        // Возможно использовать настройки из Шапок
    }

    /**
     * Задать настройки для типовых блоков.
     */
    public function setBlocks()
    {
        //Задание значения css параметра из дизайнерского режима
        // $this->setCssVal( 'метка', 'значение' );
        $this->setCssVal('page.footer.height', '390px');

        /*-Блоки в подвале-*/

        $this->setCssVal('page.footer.color_a', '#12aeba');
        $this->setCssVal('page.footer.color', '#222222');
        $this->setCssVal('page.footer.image', $this->sPathDir . '/footer-bg.jpg');
        $this->setCssVal('page.footer.repeat', 'no-repeat');
        $this->setCssVal('page.footer.position_h', 'center');
        $this->setCssVal('page.footer.position_v', 'top');

        $this->setCssVal('page.footer.grid1.h_position', 'left');
        $this->setCssVal('page.footer.grid1.h_value', '80px');
        $this->setCssVal('page.footer.grid1.v_value', '352px');
        $this->setCssVal('page.footer.grid1.width', '470px');

        $this->setCssVal('page.footer.grid2.h_position', 'left');
        $this->setCssVal('page.footer.grid2.h_value', '30px');
        $this->setCssVal('page.footer.grid2.v_value', '345px');
        $this->setCssVal('page.footer.grid2.width', '45px');

        $this->setCssVal('page.footer.grid3.h_position', 'right');
        $this->setCssVal('page.footer.grid3.h_value', '30px');
        $this->setCssVal('page.footer.grid3.v_value', '51px');
        $this->setCssVal('page.footer.grid3.width', '210px');

        $this->setCssVal('page.footer.grid4.h_position', 'left');
        $this->setCssVal('page.footer.grid4.h_value', '18.5%');
        $this->setCssVal('page.footer.grid4.v_value', '41px');
        $this->setCssVal('page.footer.grid4.width', '63%');

        $this->setCssVal('page.footer.grid5.h_position', 'left');
        $this->setCssVal('page.footer.grid5.h_value', '30px');
        $this->setCssVal('page.footer.grid5.v_value', '59px');
        $this->setCssVal('page.footer.grid5.width', '80px');

        $this->setCssVal('page.footer.grid7.h_position', 'right');
        $this->setCssVal('page.footer.grid7.h_value', '30px');
        $this->setCssVal('page.footer.grid7.v_value', '352px');
        $this->setCssVal('page.footer.grid7.width', '255px');
    }

    /**
     * Установить типовой контент
     */
    public function setContent()
    {
        $this->copyDirFiles('web/images', $this->sPathDir);

        //Задание контента блоку по метке
        $iCallbackSection = \Yii::$app->sections->getValue('callback');
        $this->setBlockText(
            'contacts',
            <<<TEXT1
        <div class="b-foot-contacts">
            <div class="foot-contacts__colleft">
                <p class="foot-contacts__phone">8 (920) 988 98 00</p>
                <p><a data-ajaxform="1" class="js-callback" href="#" data-js_max_width="600" data-section="{$iCallbackSection}">Заказать обратный звонок</a></p>
                <p><a class="foot-contacts__email" href="mailto:arconic-che@gmail.com">arconic-che@gmail.com</a></p>
            </div>
            <div class="foot-contacts__colright">
                <div class="foot-contacts__application"><a href="#">Оставить заявку</a></div>
                <div class="foot-contacts__icon">
                    <span class="foot-contacts__icon-item"><a href="#"><img src="{$this->sPathDir}/foot-icon-vk.png" alt=""></a></span>
                    <span class="foot-contacts__icon-item"><a href="#"><img src="{$this->sPathDir}/foot-icon-fb.png" alt=""></a></span>
                    <span class="foot-contacts__icon-item"><a href="#"><img src="{$this->sPathDir}/foot-icon-tw.png" alt=""></a></span>
                </div>
            </div>
        </div>
TEXT1
        );

        $this->setBlockText(
            'footertext4',
            <<<TEXT2
        <div class="b-foot-menu">
            <div class="foot-menu__inner">
                <div class="foot-menu__col">
                    <ul>
                        <li class="foot-menu__title"><a href="#">о компании</a></li>
                        <li><a href="#">История</a></li>
                        <li><a href="#">Руководство</a></li>
                        <li><a href="#">Филиалы</a></li>
                        <li><a href="#">Венчурный фонд</a></li>
                    </ul>
                </div>
                <div class="foot-menu__col">
                    <ul>
                        <li class="foot-menu__title"><a href="#">Инвесторам</a></li>
                        <li><a href="#">Документация</a></li>
                        <li><a href="#">Гарантии</a></li>
                        <li><a href="#">Рекомендации</a></li>
                    </ul>
                </div>
                <div class="foot-menu__col">
                    <ul>
                        <li class="foot-menu__title"><a href="#">Клиентам</a></li>
                        <li><a href="#">Отзывы</a></li>
                        <li><a href="#">Техподдержка</a></li>
                        <li><a href="#">Обратная связь</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>
                <div class="foot-menu__col">
                    <ul>
                        <li class="foot-menu__title"><a href="#">Пресс-центр</a></li>
                        <li><a href="#">Новости компании</a></li>
                        <li><a href="#">Новости биржи</a></li>          
                    </ul>
                </div>
            </div>
        </div>
TEXT2
        );
        $this->setBlockText('footertext5', '<p><img src="' . $this->sPathDir . '/foot-logo.png" alt=""></p>');
        $this->setBlockText('counters', '<p><img src="' . $this->sPathDir . '/live-internet.png" alt=""></p>');
        $this->setBlockText('copyright', '<p class="b-copyright">&copy; &laquoARCONIC GROUP&raquo; - крупнейший производитель алюминия в мире, [Year]</p>');
        $this->setBlockText('copyright_dev', '<p class="b-copyright-dev"><a href="https://www.web-canape.ru/razrabotka-sajta/?utm_source=copyright">Разработка</a> и <a href="https://www.web-canape.ru/prodvizhenie-sajtov/?utm_source=copyright">маркетинг</a> - WebCanape</p>');
    }
}
