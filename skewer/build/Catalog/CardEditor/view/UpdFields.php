<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 30.12.2016
 * Time: 12:29.
 */

namespace skewer\build\Catalog\CardEditor\view;

use skewer\components\ext\view\FormView;

class UpdFields extends FormView
{
    public $aLinkList;
    public $bFieldIsNotLinked;
    public $aWidgetList;
    public $iLinkId;
    public $sWidget;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSelect('link_id', \Yii::t('card', 'field_f_link_id'), $this->aLinkList, ['disabled' => $this->bFieldIsNotLinked], false)
            ->fieldSelect('widget', \Yii::t('card', 'field_f_widget'), $this->aWidgetList)
            ->setValue([
                'link_id' => $this->iLinkId,
                'widget' => $this->sWidget,
            ]);
    }
}
