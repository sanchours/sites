<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 27.02.2017
 * Time: 19:16.
 */

namespace skewer\build\Adm\Files\view;

use skewer\components\ext\view\FileView;

class PreviewList extends FileView
{
    public $bCanSelect;

    protected function getLibFileName()
    {
        return 'FileBrowserImages';
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        if ($this->bCanSelect) {
            $this->_form
                ->button('', \Yii::t('Files', 'select'), 'icon-commit', 'selectFile')
                ->buttonSeparator();
        }

        $this->_form
            ->button('addForm', \Yii::t('Files', 'load'), 'icon-add')
            ->button('', \Yii::t('Files', 'showFilesLink'), 'icon-link', 'copy_filelink', ['unsetFormDirtyBlocker' => true])
            ->buttonSeparator('->')
            ->buttonDelete('');
    }
}
