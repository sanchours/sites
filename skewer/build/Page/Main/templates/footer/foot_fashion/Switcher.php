<?php

namespace skewer\build\Page\Main\templates\footer\foot_fashion;

use skewer\components\design\TplSwitchFooter;

class Switcher extends TplSwitchFooter
{
    public $sPathDir = '/files/foot_fashion/images';

    /**
     * Отдает имя название шаблона.
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Подвал Мода';
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

        $this->setCssVal('page.footer.height', '841px');
        $this->setCssVal('page.footer.image', $this->sPathDir . '/fonfooter.png');
        $this->setCssVal('page.footer.color', '#fff');
        $this->setCssVal('page.footer.footerboxl.color', 'transparent');
        $this->setCssVal('page.footer.footerboxr.color', 'transparent');
        $this->setCssVal('page.footer.footerboxl.image', $this->sPathDir . '/fonfooter.png');
        $this->setCssVal('page.footer.footerboxr.image', $this->sPathDir . '/fonfooter.png');
        $this->setCssVal('page.footer.color_t', '#181818');
        $this->setCssVal('page.footer.color_a', '#181818');

        /*-Блоки в подвале-*/
        $this->setCssVal('page.footer.grid1.h_position', 'left');
        $this->setCssVal('page.footer.grid1.h_value', '30px');
        $this->setCssVal('page.footer.grid1.v_value', '71px');
        $this->setCssVal('page.footer.grid1.width', '190px');

        $this->setCssVal('page.footer.grid2.h_value', '-1000px');

        $this->setCssVal('page.footer.grid3.h_position', 'right');
        $this->setCssVal('page.footer.grid3.h_value', '30px');
        $this->setCssVal('page.footer.grid3.v_value', '44px');
        $this->setCssVal('page.footer.grid3.width', '250px');

        $this->setCssVal('page.footer.grid4.h_position', 'right');
        $this->setCssVal('page.footer.grid4.h_value', '0');
        $this->setCssVal('page.footer.grid4.v_value', 'auto');
        $this->setCssVal('page.footer.grid4.width', 'auto');

        $this->setCssVal('page.footer.grid5.h_position', 'right');
        $this->setCssVal('page.footer.grid5.h_value', '0');
        $this->setCssVal('page.footer.grid5.v_value', '40px');
        $this->setCssVal('page.footer.grid5.width', '470px');

        $this->setCssVal('page.footer.grid7.h_position', 'right');
        $this->setCssVal('page.footer.grid7.h_value', '0');
        $this->setCssVal('page.footer.grid7.v_value', '872px');
        $this->setCssVal('page.footer.grid7.width', '360px');
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
    <a href="#"><img alt="" src="{$this->sPathDir}/icon-home.png" /></a>
    <a href="#"><img alt="" src="{$this->sPathDir}/icon-messenger.png" /></a>
    <a href="#"><img alt="" src="{$this->sPathDir}/icon-map.png" /></a> 
</div>
<div class="b-foot-contacts">
<div class="b-foot-phone"><a href="tel:79005555005">+7 900 555 50 05</a>
</div>
<p><a href="mailto:fashionuniform@gmail.com">fashionuniform@gmail.com</a></p>
</div>

TEXT1
        );

        $this->setBlockText(
            'footertext4',
            <<<TEXT2
<div class="b-foot-menu">
<div class="foot-menu__item"><span><a href="#">Женщинам</a></span>
    <ul>
        <li><a href="#">Шубы</a></li>
        <li><a href="#">Платья</a></li>
        <li><a href="#">Блузки</a></li>
        <li><a href="#">Юбки</a></li>
        <li><a href="#">Туфли</a></li> 
    </ul>
</div>
<div class="foot-menu__item"><span><a href="#">Мужчинам</a></span>
    <ul>
        <li><a href="#">Куртки</a></li>
        <li><a href="#">Пиджаки</a></li>
        <li><a href="#">Рубашки</a></li>
        <li><a href="#">Брюки</a></li>
        <li><a href="#">Ботинки</a></li> 
    </ul>
</div>

<div class="foot-menu__item"><span><a href="#">Подросткам</a></span>

<ul>
	<li><a href="#">Пуховики</a></li>
	<li><a href="#">Джинсы</a></li>
	<li><a href="#">Свитшоты</a></li>
	<li><a href="#">Футболки</a></li>
	<li><a href="#">Кроссовки</a></li>
</ul>
</div>

<div class="foot-menu__item"><span><a href="#">Детям</a></span>

<ul>
	<li><a href="#">Ползунки</a></li>
	<li><a href="#">Костюмы</a></li>
	<li><a href="#">Рубашки</a></li>
	<li><a href="#">Комбинезоны</a></li>
	<li><a href="#">Ботинки</a></li>
</ul>
</div>
</div>
TEXT2
);

        $this->setBlockText(
            'footertext5',
            <<<TEXT3
<div class="b-footer-logo"><img alt="logofooter" src="{$this->sPathDir}/logofooter.png" /></div>

<div class="b-footer-place"><img alt="place" src="{$this->sPathDir}/place.jpg" />
<div class="footer__text">
<p>This list of famous male fashion designers is ranked of prominence. This greatest male fashion designers list contains ...</p>
</div>
</div>

TEXT3
);

        // $this->setBlockText('footertext5', '');
        $this->setBlockText('counters', '<span class="counter__item"><img alt="" src="' . $this->sPathDir . '/counter_blank.gif" width="88" height="31" /></span>');
        $this->setBlockText('copyright', '<p>&copy; &laquo;Копирайт&raquo;, [Year]</p>');
        $this->setBlockText('copyright_dev', '<div class="b-copy">
            <div class="copy__link">
               <span><img src="' . $this->sPathDir . '/webcanape-logo.png"></span> <a href="https://www.web-canape.ru/razrabotka-sajta/?utm_source=copyright">Разработка</a> и <a href="https://www.web-canape.ru/prodvizhenie-sajtov/?utm_source=copyright">маркетинг</a> - WebCanape
            </div>
    </div>');
    }
}
