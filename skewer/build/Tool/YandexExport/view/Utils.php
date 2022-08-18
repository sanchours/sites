<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 16.02.2017
 * Time: 18:33.
 */

namespace skewer\build\Tool\YandexExport\view;

use skewer\components\ext\view\ListView;

class Utils extends ListView
{
    public $aFieldNameTitle;
    public $aTree;
    public $aEditableFields;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list->field('title', \Yii::t('yandexExport', 'section'), 'string', ['listColumns' => ['flex' => 3]]);

        foreach ($this->aFieldNameTitle as $name => $title) {
            $this->_list->field($name, $title, 'check', ['listColumns' => ['flex' => 1]]);
        }

        $this->_list->buttonRow('saveParam', \Yii::t('adm', 'save'), 'icon-save', 'add')
            ->setValue($this->aTree)
            ->setEditableFields($this->aEditableFields, 'saveCheck')
            ->buttonCancel();
    }
}
