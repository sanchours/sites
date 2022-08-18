<?php
/**
 * Created by PhpStorm.
 * User: ram
 * Date: 16.05.2018
 * Time: 10:10.
 */

namespace skewer\build\Adm\Catalog\view;

use skewer\components\ext\view\FormView;

class SetCollectionField extends FormView
{
    /** @var array */
    public $collectionFields;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSelect('collectionField', \Yii::t('catalog', 'goods_collection'), $this->collectionFields, [], false)
            ->setValue([])
            ->buttonSave('saveConfig');
    }
}
