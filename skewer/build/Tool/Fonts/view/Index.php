<?php

namespace skewer\build\Tool\Fonts\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $aFontsFamily;
    public $bNotInCluster;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_list
            ->fieldString('name', \Yii::t('fonts', 'family_fonts'), ['listColumns' => ['flex' => 3]])
            ->fieldCheck('active', \Yii::t('fonts', 'connection'), ['listColumns' => ['flex' => 1]])
            ->setValue($this->aFontsFamily)
            ->setEditableFields(['active'], 'toggleActive')
            ->buttonAddNew('addFont', 'Добавить');

        $this->_list
            ->buttonSeparator('->');

        $this->_list
            ->buttonAddNew('addFolder', 'Создать папку');

        $this->_list
            ->buttonRowUpdate()
            ->buttonRowDelete();

        $this->_list->setHighlighting('correct', 'Ошибка подключения шрифта', '0');
    }
}
