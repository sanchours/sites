<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 10.01.2017
 * Time: 18:35.
 */

namespace skewer\build\Catalog\Goods\view;

use skewer\build\Catalog\Goods\model\GoodsEditor;
use skewer\components\ext\view\FormView;

class LoadSection extends FormView
{
    public $sMainLinkField;
    public $aSectionList;
    public $iSection;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSelect(
                $this->sMainLinkField,
                \Yii::t('catalog', 'main_section'),
                $this->aSectionList,
                ['groupTitle' => \Yii::t('catalog', 'settings')],
                false
            )
            ->setValue([
                GoodsEditor::mainLinkField => $this->iSection,
            ]);
    }
}
