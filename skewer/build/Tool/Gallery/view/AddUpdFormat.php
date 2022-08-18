<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 18.01.2017
 * Time: 11:29.
 */

namespace skewer\build\Tool\Gallery\view;

use skewer\components\ext\view\FormView;

class AddUpdFormat extends FormView
{
    public $aWatermarkCalibrateList;
    public $aValues;
    public $bCanDelete;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_form
            ->fieldString('title', \Yii::t('gallery', 'formats_title'))
            ->fieldString('name', \Yii::t('gallery', 'formats_name'))
            ->field('width', \Yii::t('gallery', 'formats_width'), 'int', ['minValue' => 0, 'allowDecimals' => false])
            ->field('height', \Yii::t('gallery', 'formats_height'), 'int', ['minValue' => 0, 'allowDecimals' => false])
            ->field('active', \Yii::t('gallery', 'formats_active'), 'check')
            ->fieldHide('id', '')
            ->field('resize_on_larger_side', \Yii::t('gallery', 'formats_resize_on_larger_side'), 'check')
            ->field('scale_and_crop', \Yii::t('gallery', 'formats_scale_and_crop'), 'check')
            ->field('use_watermark', \Yii::t('gallery', 'formats_use_watermark'), 'check')
            ->field('watermark', \Yii::t('gallery', 'formats_watermark'), 'file')
            ->fieldHide('profile_id', \Yii::t('gallery', 'formats_profile_id'), 'i')
            ->fieldSelect('watermark_align', \Yii::t('gallery', 'formats_watermark_align'), $this->aWatermarkCalibrateList, [], false)
            ->fieldHide('position', \Yii::t('gallery', 'formats_position'), 'i')
            ->setValue($this->aValues)
            ->buttonSave('saveFormat')
            ->buttonCancel('formatsList');

        if ($this->bCanDelete) {
            $this->_form->buttonSeparator('->')
                ->buttonDelete('delFormat');
        }
    }
}
