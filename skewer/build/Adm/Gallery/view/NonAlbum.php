<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 28.02.2017
 * Time: 17:14.
 */

namespace skewer\build\Adm\Gallery\view;

use skewer\components\ext;
use skewer\components\ext\view\ListView;

class NonAlbum extends ListView
{
    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list->buttonCustomExt(ext\docked\AddBtn::create()->setAction('CreateAlbum4Section'));
    }
}
