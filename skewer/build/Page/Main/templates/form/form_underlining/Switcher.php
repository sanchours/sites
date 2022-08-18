<?php

namespace skewer\build\Page\Main\templates\form\form_underlining;

use skewer\components\design\BackupFormParams;
use skewer\components\design\TplSwitchForm;
use skewer\components\forms\ApiField;

class Switcher extends TplSwitchForm
{
    public function __construct()
    {
        $this->oBackup = new BackupFormParams();
    }

    /**
     * Отдает имя название шаблона.
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Material Design';
    }

    /**
     * Задать настройки для типовых блоков.
     */
    public function setBlocks()
    {
        $this->setParam4FieldForm('new_line', 1);

        $this->setCommonClassForm('b-form--material');

        // задать тип вывода элементам
        $this->setFieldType('radio', '1');
        $this->setFieldType('checkbox', '2');
        $this->setFieldType('checkboxGroup', '2');

        $this->setClassField('checkbox', '');
        $this->setClassField('checkboxGroup', '');

        $this->setClassField('calendar', 'form__item--datepicker-full');

        // задать положение вывода подписи
        $this->setPositionLabel(ApiField::LABEL_POSITION_LEFT);

        $this->setCssVal('modules.forms.padding', '35px 50px');
        $this->setCssVal('modules.forms.width_border', '5px 0 0');
        $this->setCssVal('modules.forms.color_border', '#2199ff');

        $this->setCssVal('modules.forms.header.border_width', '0');
        $this->setCssVal('modules.forms.header.header_font_size', '30px');
        $this->setCssVal('modules.forms.header.header_font_weight', 'bold');

        $this->setCssVal('modules.forms.elements.input_padding', '20px 0');
        $this->setCssVal('modules.forms.elements.input_border_width', '0 0 1px');

        $this->setCssVal('modules.forms.labels.font_size', '16px');
        $this->setCssVal('modules.forms.labels.label_width', '210px');
    }

    /**
     * Установить типовой контент
     */
    public function setContent()
    {
    }

    public static function getBackupClass()
    {
        return BackupFormParams::className();
    }
}
