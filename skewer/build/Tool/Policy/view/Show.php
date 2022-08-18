<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 19.01.2017
 * Time: 16:31.
 */

namespace skewer\build\Tool\Policy\view;

use skewer\components\ext\view\FormView;

class Show extends FormView
{
    public $bHide;
    public $sAlias;
    public $sAreaListTitles;
    public $sArea;
    public $aParams;
    public $aParamsModule;
    public $iItemId;
    public $aItem;
    public $bIsUsualPolicy;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldHide('id')
            ->fieldHide('access_level')
            ->fieldString('title', \Yii::t('auth', 'policytitle'));

        if ($this->bHide) {
            $this->_form
                ->fieldString('alias1', \Yii::t('auth', 'alias'), ['disabled' => true, 'value' => $this->sAlias])
                ->fieldSelect('area1', \Yii::t('auth', 'area'), $this->sAreaListTitles, ['disabled' => true, 'value' => $this->sArea], false)
                ->fieldHide('alias')
                ->fieldHide('area')
                ->fieldHide('fulladmin')
                ->fieldHide('active');
        } else {
            $this->_form
                ->fieldString('alias', \Yii::t('auth', 'alias'))
                ->fieldSelect('area', \Yii::t('auth', 'area'), $this->sAreaListTitles, [], false)
                ->field('fulladmin', \Yii::t('auth', 'extended_rights'), $this->bHide ? 'hide' : 'check', ['subtext' => \Yii::t('auth', 'extended_rights_text')])
                ->field('active', \Yii::t('auth', 'active'), $this->bHide ? 'hide' : 'check');
        }

        $this->_form
            ->field('params', '', 'specific', $this->aParams)
            ->addLib('CheckSet')

            ->field('params_module', '', 'specific', $this->aParamsModule)
            ->addLib('CheckSet4Module')

            ->buttonSave()
            ->buttonIf($this->iItemId, \Yii::t('auth', 'sections'), 'sections', 'icon-edit', '', ['addParams' => ['id' => $this->iItemId]])
            ->buttonCancel()

            ->setValue($this->aItem);

        if ($this->bIsUsualPolicy) {
            $this->_form
                ->buttonSeparator('->')
                ->buttonDelete();
        }
    }
}
