<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 28.12.2016
 * Time: 15:03.
 */

namespace skewer\build\Catalog\Collections\view;

use skewer\components\ext\view\FormView;

class Edit extends FormView
{
    public $aActiveProfiles;
    public $oCard;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->headText('<h1>' . \Yii::t('collections', 'new_coll') . '</h1>')
            ->fieldHide('id', 'id')
            ->fieldString('title', \Yii::t('collections', 'coll_name'), ['listColumns.flex' => 1])
            ->fieldString('name', \Yii::t('collections', 'system_name'))
            ->fieldSelect('profile_id', \Yii::t('collections', 'field_gallery_profile'), $this->aActiveProfiles, [], false)
            ->setValue($this->oCard)
            ->buttonSave('Save')
            ->buttonCancel('List');
    }
}
