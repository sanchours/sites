<?php

namespace skewer\build\Page\Main\templates\form\form_field_group;

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
        return 'Широкая рамка';
    }

    /**
     * Задать настройки для типовых блоков.
     */
    public function setBlocks()
    {
        $this->setParam4FieldForm('new_line', 1);

        $this->setCommonClassForm('b-form--group');

        // задать тип вывода элементам
        $this->setFieldType('radio', '1');
        $this->setFieldType('checkbox', '2');
        $this->setFieldType('checkboxGroup', '2');

        // задать положение вывода подписи
        $this->setPositionLabel(ApiField::LABEL_POSITION_NONE);

        $this->setCssVal('modules.forms.padding', '35px 50px');
        $this->setCssVal('modules.forms.width_border', '5px');
        $this->setCssVal('modules.forms.color_border', '#2199ff');

        $this->setCssVal('modules.forms.header.border_width', '0');
        $this->setCssVal('modules.forms.header.header_font_size', '30px');
        $this->setCssVal('modules.forms.header.header_font_weight', 'bold');
        $this->setCssVal('modules.forms.header.text_align', 'center');

        $this->setCssVal('modules.forms.labels.font_size', '16px');
        $this->setCssVal('modules.forms.labels.font_weight', 'normal');

        $this->setCssVal('modules.forms.elements.input_padding', '25px 30px');
    }

    public static function getBackupClass()
    {
        return BackupFormParams::className();
    }
}
