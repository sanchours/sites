<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 28.02.2017
 * Time: 15:08.
 */

namespace skewer\build\Adm\Gallery\view;

use skewer\components\ext\view\FormView;
use skewer\components\seo;

class AddUpdAlbum extends FormView
{
    public $aActiveProfiles;
    public $iAlbumId;
    public $aValues;
    public $oSeo;
    public $iSectionId = 0;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldHide('owner', \Yii::t('gallery', 'owner'), 's')
            ->fieldString('title', \Yii::t('gallery', 'title'))
            ->fieldString('alias', \Yii::t('gallery', 'alias'))
            ->field('visible', \Yii::t('gallery', 'visible'), 'check')
            ->fieldHide('id', \Yii::t('gallery', 'album_id'))
            ->fieldHide('priority', \Yii::t('gallery', 'priority'))
            ->fieldSelect('profile_id', \Yii::t('gallery', 'profiles_select'), $this->aActiveProfiles, [], false)
            ->fieldHide('section_id', \Yii::t('gallery', 'section_id'))
            ->field('description', \Yii::t('gallery', 'description'), 'text');

        if ($this->iAlbumId) {
            $this->_form->fieldString('creation_date', \Yii::t('gallery', 'creation_date'), ['disabled' => true]);
        }

        if ($this->aValues['id']) {
            $this->_form->fieldShow('profile_id', \Yii::t('gallery', 'profile_id'));
        }

        $this->_form
            ->setValue($this->aValues)
            ->buttonSave('saveAlbum');

        seo\Api::appendExtForm($this->_form, $this->oSeo, $this->iSectionId, ['seo_gallery', 'none_search']);

        if ($this->iAlbumId) {
            $this->_form->buttonCancel('showAlbum');
        } else {
            $this->_form->buttonCancel('getAlbums');
        }

        $this->_form->getForm()->setModuleLangValues(['galleryUploadingImage']);
    }
}
