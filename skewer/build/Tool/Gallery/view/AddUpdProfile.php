<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 17.01.2017
 * Time: 18:59.
 */

namespace skewer\build\Tool\Gallery\view;

use skewer\components\ext\view\FormView;

class AddUpdProfile extends FormView
{
    public $aProfileTypes;
    public $aItem;
    public $iProfileId;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldHide('id', 'id')
            ->fieldSelect('type', \Yii::t('gallery', 'profiles_type'), $this->aProfileTypes, [], false)
            ->fieldString('title', \Yii::t('gallery', 'profiles_title'))
            ->fieldString('alias', \Yii::t('gallery', 'profiles_alias'))
            ->fieldColor('watermark_color', \Yii::t('gallery', 'watermark_color'))
            ->setValue($this->aItem)
            ->buttonSave('saveProfile')
            ->buttonBack();
        if ($this->iProfileId) {
            $this->_form->buttonEdit('formatsList', \Yii::t('gallery', 'tools_formats'));
        }
    }
}
