<?php
/**
 * Created by PhpStorm.
 * User: ram
 * Date: 14.05.2018
 * Time: 16:59.
 */

namespace skewer\build\Tool\GalleryOnPage\view;

use skewer\components\ext\view\FormView;

class View extends FormView
{
    /** @var array */
    public $data;

    /** @var array */
    public $excludedParams;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form->field('class', 'class', 'hide');

        /*Если НЕ работает модуль режима адаптивности, скроем параметр адаптивности*/
        $oInstaller = new \skewer\components\config\installer\Api();

        if (!$oInstaller->isInstalled('AdaptiveMode', \skewer\base\site\Layer::PAGE)) {
            $this->_form
                ->field('responsive', \Yii::t('GalleryOnPage', 'items'), 'hide')
                ->field('items', \Yii::t('GalleryOnPage', 'items'), in_array('items', $this->excludedParams) ? 'hide' : 'int');
        } else {
            $this->_form
                ->field('responsive', \Yii::t('GalleryOnPage', 'items'), in_array('responsive', $this->excludedParams) ? 'hide' : 'string')
                ->field('items', \Yii::t('GalleryOnPage', 'items'), 'hide');
        }

        if ( in_array('margin', $this->excludedParams) ){
            $this->_form->fieldHide('margin', \Yii::t('GalleryOnPage', 'margin'));
        } else {
            $this->_form->fieldInt('margin', \Yii::t('GalleryOnPage', 'margin'), ['minValue' => 0]);
        }

        $this->_form
            ->field('nav', \Yii::t('GalleryOnPage', 'nav'), in_array('nav', $this->excludedParams) ? 'hide' : 'check')
            ->field('dots', \Yii::t('GalleryOnPage', 'dots'), in_array('dots', $this->excludedParams) ? 'hide' : 'check')
            ->field('autoWidth', \Yii::t('GalleryOnPage', 'autoWidth'), in_array('autoWidth', $this->excludedParams) ? 'hide' : 'check')
            ->field('loop', \Yii::t('GalleryOnPage', 'loop'), in_array('loop', $this->excludedParams) ? 'hide' : 'check')
            ->field('slideBy', \Yii::t('GalleryOnPage', 'slideBy'), in_array('slideBy', $this->excludedParams) ? 'hide' : 'select', ['show_val' => [
                '1' => '1',
                'page' => \Yii::t('GalleryOnPage', 'slideByPage'),
            ], 'emptyStr' => false])

            ->field('shadow', \Yii::t('GalleryOnPage', 'shadow'), 'check', ['disabled' => in_array('shadow', $this->excludedParams)])
            ->field('autoHeight', \Yii::t('GalleryOnPage', 'shadow'), 'hide')

            ->buttonSave('save')
            ->buttonCancel('Init')

            ->setValue($this->data);
    }
}
