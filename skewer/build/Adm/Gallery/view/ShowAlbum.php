<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 28.02.2017
 * Time: 12:07.
 */

namespace skewer\build\Adm\Gallery\view;

use skewer\components\ext\docked;
use skewer\components\ext\view\FileView;

class ShowAlbum extends FileView
{
    public $notOnlyAlbumEditor;
    public $popup;

    protected function getLibFileName()
    {
        return 'PhotoList';
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        if ($this->notOnlyAlbumEditor) {
            $this->_form
                ->button('addUpdAlbum', \Yii::t('gallery', 'module_addUpdAlbum'), 'icon-edit')
                ->button('getAlbums', \Yii::t('gallery', 'module_getAlbums'), 'icon-cancel')
                ->buttonSeparator('');
        }

        if ($this->popup) {
            $this->_form->buttonSave('', null, ['state' => 'closeWindow']);
        }

        $this->_form->getForm()->setModuleLangValues([
            'galleryActive',
            'galleryDelImg',
            'galleryNoItems',
            'galleryDeleteConfirm',
            'galleryMultiDelImg',
            'galleryNoImages',
            'galleryUploadingImage',
        ]);

        $this->_form
            ->buttonCustomExt(docked\UserFile::create(\Yii::t('gallery', 'module_loadImage'), 'PhotoAddField')->setIconCls(docked\Api::iconAdd))
            ->buttonSeparator()
            ->buttonDelete('', null, [
                'unsetFormDirtyBlocker' => true,
                'state' => 'del_selected',
            ]);
    }
}
