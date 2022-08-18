<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 28.02.2017
 * Time: 13:52.
 */

namespace skewer\build\Adm\Gallery\view;

use skewer\components\ext;
use skewer\components\ext\view\FormView;
use skewer\components\seo;

class ShowImage extends FormView
{
    public $aTabs;
    public $iActiveTabIndex;
    public $aImage;
    public $iImageId;
    public $bShowStubSeo;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSpec('formats', \Yii::t('gallery', 'module_images'), 'PhotoShowFormatsField', $this->aTabs, ['activeTab' => $this->iActiveTabIndex])
            ->field('visible', \Yii::t('gallery', 'module_showInAlbum'), 'check');

        seo\Api::appendSeoBlock4Gallery($this->_form, $this->bShowStubSeo);

        $this->_form
            ->field('description', \Yii::t('gallery', 'module_description'), 'text')
            ->fieldString('creation_date', \Yii::t('gallery', 'module_creation_date'), ['disabled' => true])
            ->fieldHide('id', '')
            ->setValue($this->aImage)
            ->buttonSave('updateImage')
            ->buttonCancel('showAlbum')
            ->buttonSeparator('-')
            ->button('reCropForm', 'Re-crop', 'icon-edit')
            ->buttonCustomExt(
                ext\docked\UserFile::create(\Yii::t('gallery', 'module_edit'), 'PhotoAddToFormatField')
                    ->setIconCls(ext\docked\Api::iconEdit)
                    ->setAddParam('imageId', $this->iImageId)
            )
            ->buttonSeparator()
            ->buttonDelete('deleteImage');
    }
}
