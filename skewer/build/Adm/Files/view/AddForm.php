<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 28.02.2017
 * Time: 11:03.
 */

namespace skewer\build\Adm\Files\view;

use skewer\components\ext\view\FileView;

class AddForm extends FileView
{
    protected function getLibFileName()
    {
        return 'FileAddForm';
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_form
            ->button('', \Yii::t('Files', 'load'), 'icon-commit', 'upload')
            ->buttonSeparator()
            ->buttonCancel();
    }
}
