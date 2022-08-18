<?php

namespace skewer\build\Page\Main\templates\form\base;

use skewer\components\design\TplSwitchForm;
use skewer\components\forms\ApiField;

class Switcher extends TplSwitchForm
{
    /**
     * Отдает имя название шаблона.
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Базовый';
    }

    /**
     * Задать настройки для типовых блоков.
     */
    public function setBlocks()
    {
        $this->setCommonClassForm('b-form-default');

        $this->setFieldType('checkbox', '1');
        $this->setFieldType('checkboxGroup', '1');
        $this->setFieldType('radio', '1');
        $this->setPositionLabel(ApiField::LABEL_POSITION_TOP);

        $this->setClassField('checkboxGroup', 'form__item--el-col-4');
        $this->setClassField('radio', 'form__item--el-col-4');
    }
}
