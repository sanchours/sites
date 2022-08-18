<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 29.12.2016
 * Time: 15:01.
 */

namespace skewer\build\Catalog\Collections\view;

use skewer\components\ext\view\FormView;

class EditCollection extends FormView
{
    public $aProfiles;
    public $oDict;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldHide('id', 'id')
            ->fieldString('title', \Yii::t('collections', 'field_title'), ['listColumns.flex' => 1])
            ->fieldSelect('profile_id', \Yii::t('collections', 'field_gallery_profile'), $this->aProfiles, [], false)
            ->setValue($this->oDict)
            ->buttonSave('SaveCollection')
            ->buttonCancel('View');
    }
}
