<?php

namespace skewer\build\Adm\Gallery\view;

use skewer\build\Adm;
use skewer\build\Page;
use skewer\components\ext\view\FormView;

class SettingsInSection extends FormView
{
    public $template_detail;
    public $sWarning;

    public function build()
    {
        /** @var Adm\Gallery\Module $oModule */
        $oModule = $this->_module;

        $this->_form
            ->fieldSelect('template_detail', \Yii::t('gallery', 'detailTemplate'), Page\Gallery\Module::getDetailTemplates(), ['onUpdateAction' => 'updateSettingsInSection'], false)
            ->fieldCheck('openAlbum', \Yii::t('gallery', 'openAlbum'));

        $aData = [
            'openAlbum' => $oModule->getParamValue('openAlbum'),
        ];

        $aDataByAjaxUpdated = ['template_detail' => $this->template_detail];

        if ($this->template_detail == Page\Gallery\Module::ALBUM_DETAIL_TPL_INLINE) {
            $this->_form
                ->fieldInt('justifiedGalleryOption_rowHeight', \Yii::t('gallery', 'justifiedGalleryOption_rowHeight'), ['minValue' => 100])
                ->fieldInt('justifiedGalleryOption_maxRowHeight', \Yii::t('gallery', 'justifiedGalleryOption_maxRowHeight'), ['minValue' => 100])
                ->fieldCheck('justifiedGalleryOption_randomize', \Yii::t('gallery', 'justifiedGalleryOption_randomize'));

            $aConfig = Adm\Gallery\Api::getConfigJustifiedGallery($oModule->sectionId(), true);
            $aData += $aConfig;

            if ($this->sWarning) {
                $this->_form
                    ->fieldWithValue('rr', \Yii::t('gallery', 'warning'), 'show', $this->sWarning);
            }
        }

        $aData = array_merge($aData, $aDataByAjaxUpdated);

        $this->_form
            ->setValue($aData)
            ->buttonSave('saveSettings')
            ->buttonCancel('getAlbums');
    }
}
