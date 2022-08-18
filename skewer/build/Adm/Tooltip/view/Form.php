<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 28.02.2017
 * Time: 15:08.
 */

namespace skewer\build\Adm\Tooltip\view;

use skewer\components\ext\view\FormView;

class Form extends FormView
{
    public $iTooltipId;
    public $aValues;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldHide('id', 'ID')
            ->fieldString('name', \Yii::t('tooltip', 'field_name'))
            ->fieldWysiwyg('text', \Yii::t('tooltip', 'field_text'), 400);

        $this->_form
            ->setValue($this->aValues)
            ->buttonSave('save');

        $this->_form->buttonCancel('getList');

        $this->_form->useSpecSectionForImages(\Yii::$app->sections->getValue('tooltip'));

        $this->_form->getForm()->setModuleLangValues(['galleryUploadingImage']);
    }
}
