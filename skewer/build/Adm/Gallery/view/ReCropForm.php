<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 28.02.2017
 * Time: 14:46.
 */

namespace skewer\build\Adm\Gallery\view;

use skewer\components\ext\view\FormView;

class ReCropForm extends FormView
{
    public $aTabs;
    public $iImageId;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSpec('formats', \Yii::t('gallery', 'module_images'), 'PhotoResizerList', $this->aTabs)
            ->fieldHide('id', '', 'i', ['value' => $this->iImageId])
            ->setValue([])
            ->buttonSave('saveReCropImage')
            ->buttonCancel('showAlbum')
            ->button('backToDefault', \Yii::t('gallery', 'backToDefault'));
    }
}
