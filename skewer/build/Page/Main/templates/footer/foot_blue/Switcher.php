<?php

namespace skewer\build\Page\Main\templates\footer\foot_blue;

use skewer\components\design\TplSwitchFooter;

class Switcher extends TplSwitchFooter
{
    public $sPathDir = '/files/foot_blue/images';

    /**
     * Отдает имя название шаблона.
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Синий подвал';
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

        $this->setCssVal('page.footer.height', '300px');
        $this->setCssVal('page.footer.color', '#333a4b');
        $this->setCssVal('page.footer.footerboxl.color', '#333a4b');
        $this->setCssVal('page.footer.footerboxr.color', '#333a4b');
        $this->setCssVal('page.footer.size', '14px');
        $this->setCssVal('page.footer.color_t', '#a6acbb');

        /*-Блоки в подвале-*/

        $this->setCssVal('page.footer.color_a', '#a6acbb');

        $this->setCssVal('page.footer.grid1.h_position', 'left');
        $this->setCssVal('page.footer.grid1.h_value', '20px');
        $this->setCssVal('page.footer.grid1.v_value', '325px');
        $this->setCssVal('page.footer.grid1.width', '220px');

        $this->setCssVal('page.footer.grid2.h_position', 'right');
        $this->setCssVal('page.footer.grid2.h_value', '20px');
        $this->setCssVal('page.footer.grid2.v_value', '320px');
        $this->setCssVal('page.footer.grid2.width', '100px');

        $this->setCssVal('page.footer.grid3.h_position', 'left');
        $this->setCssVal('page.footer.grid3.h_value', '20px');
        $this->setCssVal('page.footer.grid3.v_value', '30px');
        $this->setCssVal('page.footer.grid3.width', '220px');

        $this->setCssVal('page.footer.grid4.h_position', 'right');
        $this->setCssVal('page.footer.grid4.h_value', '20px');
        $this->setCssVal('page.footer.grid4.v_value', '30px');
        $this->setCssVal('page.footer.grid4.width', '70%');

        $this->setCssVal('page.footer.grid7.h_position', 'right');
        $this->setCssVal('page.footer.grid7.h_value', '130px');
        $this->setCssVal('page.footer.grid7.v_value', '325px');
        $this->setCssVal('page.footer.grid7.width', '370px');
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
<div class="b-foot-btn"><a href="#">Заказать услуги</a></div>
<div class="b-foot-contacts">
<div class="b-foot-phone"><a href="tel:88005000000">8 800 500 00 00</a></div>
    <p>Смоленск, ул. Иванова, д. 1</p>
    <p>E-mail: <a href="mailto:test@test.ru">test@test.ru</a></p>
</div>
<div class="b-foot-social">
    <a href="#"><img alt="" src="{$this->sPathDir}/icon-vk.png" /></a>
    <a href="#"><img alt="" src="{$this->sPathDir}/icon-fb.png" /></a>
    <a href="#"><img alt="" src="{$this->sPathDir}/icon-od.png" /></a>
    <a href="#"><img alt="" src="{$this->sPathDir}/icon-inst.png" /></a>
</div>
TEXT1
        );

        $this->setBlockText(
            'footertext4',
            <<<TEXT2
<div class="b-foot-menu">
<div class="foot-menu__item"><span><a href="#">Мобильная связь</a></span>
    <ul>
        <li><a href="#">Тарифы</a></li>
        <li><a href="#">Мобильный интернет</a></li>
        <li><a href="#">Роуминг и межгород</a></li>
        <li><a href="#">Услуги</a></li>
        <li><a href="#">Поддержка</a></li>
        <li><a href="#">Отправить SMS/MMS</a></li>
    </ul>
</div>
<div class="foot-menu__item"><span><a href="#">Интернет и ТВ</a></span>
    <ul>
        <li><a href="#">Интернет</a></li>
        <li><a href="#">ТВ</a></li>
        <li><a href="#">Телефон</a></li>
        <li><a href="#">Спутниковое ТВ</a></li>
        <li><a href="#">Оборудование</a></li>
        <li><a href="#">Настройки</a></li>
        <li><a href="#">Поддержка</a></li>
    </ul>
</div>

<div class="foot-menu__item"><span><a href="#">Финансовые услуги</a></span>

<ul>
	<li><a href="#">Банковские услуги</a></li>
	<li><a href="#">Легкий платеж</a></li>
	<li><a href="#">Автоплатеж</a></li>
	<li><a href="#">Поддержка</a></li>
	<li><a href="#">МТС Банк</a></li>
</ul>
</div>

<div class="foot-menu__item"><span><a href="#">Поддержка</a></span>

<ul>
	<li><a href="#">Подключенные услуги</a></li>
	<li><a href="#">Действие со счетом</a></li>
	<li><a href="#">Обслуживание абонентов</a></li>
	<li><a href="#">Полезная информация</a></li>
	<li><a href="#">Опрос</a></li>
</ul>
</div>
</div>
TEXT2
);

        $this->setBlockText('footertext5', '');
        $this->setBlockText('counters', '<span class="counter__item"><img alt="" src="' . $this->sPathDir . '/counter_blank.gif" width="88" height="31" /></span>');
        $this->setBlockText('copyright', '<p>&copy; &laquo;Копирайт&raquo;, [Year]</p>');
        $this->setBlockText('copyright_dev', '<p><a href="https://www.web-canape.ru/razrabotka-sajta/?utm_source=copyright">Разработка</a> и <a href="https://www.web-canape.ru/prodvizhenie-sajtov/?utm_source=copyright">маркетинг</a> - WebCanape</p>');
    }
}
