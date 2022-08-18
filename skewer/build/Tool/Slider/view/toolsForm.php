<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 21.11.2016
 * Time: 17:46.
 */

namespace skewer\build\Tool\Slider\view;

use skewer\build\Tool\Slider\Api;
use skewer\components\ext\view\FormView;

class toolsForm extends FormView
{
    public $aHeightParams;
    public $aToolData;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSelect('transition', \Yii::t('slider', 'fotorama_transition'), Api::getTransitions(), [], false)
            ->fieldInt('transitionduration', \Yii::t('slider', 'fotorama_transitionduration'))
            ->fieldInt('autoplay', \Yii::t('slider', 'fotorama_autoplay'), ['subtext' => \Yii::t('slider', 'fotorama_autoplay_subtext')])
            ->fieldInt('maxHeight', \Yii::t('slider', 'fotorama_maxHeight'))
            ->fieldCheck('loop', \Yii::t('slider', 'fotorama_loop'))
            ->fieldInt('minHeight1280', \Yii::t('slider', 'minHeight1280'), ['minValue' => 0, 'allowDecimals' => false])
            ->fieldInt('minHeight1024', \Yii::t('slider', 'minHeight1024'), ['minValue' => 0, 'allowDecimals' => false])
            ->fieldInt('minHeight768', \Yii::t('slider', 'minHeight768'), ['minValue' => 0, 'allowDecimals' => false])
            ->fieldInt('minHeight350', \Yii::t('slider', 'minHeight350'), ['minValue' => 0, 'allowDecimals' => false])
            ->setValue($this->aToolData)
            ->buttonSave('saveTools')
            ->buttonCancel('bannerList');
    }
}
