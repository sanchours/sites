<?php

namespace skewer\build\Page\Main\templates\footer\foot_bakery;

use skewer\components\design\TplSwitchFooter;

class Switcher extends TplSwitchFooter
{
    public $sPathDir = '/files/foot_bakery/images';

    /**
     * Отдает имя название шаблона.
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Подвал Пекарня';
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
        $this->setCssVal('page.footer.height', '710px');

        /*-Блоки в подвале-*/

        $this->setCssVal('page.footer.color_a', '#fff');
        $this->setCssVal('page.footer.color', '');

        $this->setCssVal('page.footer.grid1.h_position', 'left');
        $this->setCssVal('page.footer.grid1.h_value', '90px');
        $this->setCssVal('page.footer.grid1.v_value', '660px');
        $this->setCssVal('page.footer.grid1.width', '350px');

        $this->setCssVal('page.footer.grid2.h_position', 'left');
        $this->setCssVal('page.footer.grid2.h_value', '30px');
        $this->setCssVal('page.footer.grid2.v_value', '654px');
        $this->setCssVal('page.footer.grid2.width', '55px');

        $this->setCssVal('page.footer.grid3.h_position', 'right');
        $this->setCssVal('page.footer.grid3.h_value', '30px');
        $this->setCssVal('page.footer.grid3.v_value', '65px');
        $this->setCssVal('page.footer.grid3.width', '520px');

        $this->setCssVal('page.footer.grid4.h_position', 'right');
        $this->setCssVal('page.footer.grid4.h_value', '660px');
        $this->setCssVal('page.footer.grid4.v_value', '239px');
        $this->setCssVal('page.footer.grid4.width', '220px');

        $this->setCssVal('page.footer.grid5.h_position', 'right');
        $this->setCssVal('page.footer.grid5.h_value', '605px');
        $this->setCssVal('page.footer.grid5.v_value', '66px');
        $this->setCssVal('page.footer.grid5.width', '310px');

        $this->setCssVal('page.footer.grid7.h_position', 'right');
        $this->setCssVal('page.footer.grid7.h_value', '30px');
        $this->setCssVal('page.footer.grid7.v_value', '662px');
        $this->setCssVal('page.footer.grid7.width', '330px');
    }

    /**
     * Установить типовой контент
     */
    public function setContent()
    {
        $this->copyDirFiles('web/images', $this->sPathDir);

        //Задание контента блоку по метке
        $this->setBlockText(
            'contacts',
            <<<TEXT1
        <div class="b-foot-contacts">
            <p class="foot-contacts__phone">+7 (800) 555 35 35</p>
            <p>Смоленск, Коммунистическая 11</p>
            <p>Пн-Вт: 09:00 до 22:00</p>
        </div>
        <div class="b-foot-map">
            <a href="https://yandex.ru/maps/?um=constructor%3A7f2e9cb6247353c339e71cff773e3690189d8077925f8042e8efada323694ac5&amp;source=constructorStatic" target="_blank">
                <img src="https://api-maps.yandex.ru/services/constructor/1.0/static/?um=constructor%3A7f2e9cb6247353c339e71cff773e3690189d8077925f8042e8efada323694ac5&amp;width=500&amp;height=320&amp;lang=ru_RU" alt="" style="border: 0;" />
            </a>
        </div>
TEXT1
        );

        $this->setBlockText('footertext4', '<div class="b-foot-menu"><ul><li><a href="#">Пончики</a></li><li><a href="#">Мороженное</a></li><li><a href="#">Торты</a></li><li><a href="#">Печенье</a></li><li><a href="#">Пирожные</a></li><li><a href="#">Напитки</a></li></ul></div>');
        $this->setBlockText('footertext5', '<p><img src="' . $this->sPathDir . '/foot-logo.png" alt=""></p>');
        $this->setBlockText('counters', '<p><img src="' . $this->sPathDir . '/live-internet.png" alt=""></p>');
        $this->setBlockText('copyright', '<p class="b-copyright">&copy; <span class="copyright__name">Bribery Bakery</span> [Year]</p>');
        $this->setBlockText('copyright_dev', '<p class="b-copyright-dev"><a href="https://www.web-canape.ru/razrabotka-sajta/?utm_source=copyright">Разработка</a> и <a href="https://www.web-canape.ru/prodvizhenie-sajtov/?utm_source=copyright">маркетинг</a> - WebCanape</p>');
    }
}
