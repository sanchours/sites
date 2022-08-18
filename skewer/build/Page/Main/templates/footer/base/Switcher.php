<?php

namespace skewer\build\Page\Main\templates\footer\base;

use skewer\components\design\TplSwitchFooter;

class Switcher extends TplSwitchFooter
{
    public $sPathDir = '/files/base/images';

    /**
     * Отдает имя название шаблона.
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Базовая';
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
    }

    /**
     * Установить типовой контент
     */
    public function setContent()
    {
        $this->setBlockText(
            'contacts',
            <<<TEXT1
<p style="text-align: right;"><span style="text-align: right;">Адрес: Смоленск, ул. Иванова, д. 1</span><br style="text-align: right;" />
<span style="text-align: right;">E-mail: <a href="#">test@test.ru</a></span><br style="text-align: right;" />
<span style="text-align: right;">Телефоны: +7 (000) 000 00 00</span><br style="text-align: right;" />
<span style="text-align: right;">+7 (000) 000 00 00</span></p>
TEXT1
        );

        $this->copyFile('web/images/counter_blank.gif', '/files/base/counter_blank.gif');

        $this->setBlockText('footertext4', '');

        $this->setBlockText('footertext5', '');
        $this->setBlockText('counters', '<span class="counter__item"><img alt="" src="' . $this->sPathDir . '/counter_blank.gif" width="88" height="31" /></span>');
        $this->setBlockText(
            'copyright',
            <<<TEXT2
<p>&copy; &laquo;Копирайт&raquo;, [Year]</p>
<p>Популярные разделы: <a href="#">список товаров 1</a>&nbsp;| <a href="#">список товаров 2</a>&nbsp;| <a href="#">список товаров 3</a></p>
TEXT2
        );
        $this->setBlockText('copyright_dev', '<p class="b-copy"><a href="https://www.web-canape.ru/razrabotka-sajta/?utm_source=copyright">Разработка</a> и <a href="https://www.web-canape.ru/prodvizhenie-sajtov/?utm_source=copyright">маркетинг</a> - WebCanape</p>');
    }
}
