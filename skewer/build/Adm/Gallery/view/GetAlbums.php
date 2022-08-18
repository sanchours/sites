<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 28.02.2017
 * Time: 11:38.
 */

namespace skewer\build\Adm\Gallery\view;

use skewer\components\ext;
use skewer\components\ext\view\FileView;

class GetAlbums extends FileView
{
    protected function getLibFileName()
    {
        return 'PhotoAlbumList';
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_form
            ->buttonCustomExt(
                ext\docked\AddBtn::create()
                    ->setAction('addUpdAlbum')
            )
            ->button('settings', \Yii::t('gallery', 'settings'), 'icon-edit')
            ->buttonSeparator()
            ->buttonCustomExt(
                ext\docked\DelBtn::create()
                    ->setAction('')
                    ->setState('del_selected')
            );
    }
}
