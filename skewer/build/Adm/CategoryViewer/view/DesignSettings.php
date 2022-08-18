<?php

namespace skewer\build\Adm\CategoryViewer\view;

use skewer\base\site\Layer;
use skewer\base\ui\builder\FormBuilder;
use skewer\components\ext\view\FormView;
use skewer\components\fonts;

class DesignSettings extends FormView
{
    /** @var array Список параметров, определяющих состав полей интерфейса */
    public $aListParams = [];

    /** @var array Даннные */
    public $aData = [];

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        if ($this->aListParams) {
            self::buildFormByParamsArray($this->_form, $this->aListParams);
            $this->_form->buttonSave('saveDesignParameters');
        } else {
            $this->_form->headText(\Yii::t('categoryViewer', 'noParamsForSection'));
        }

        $this->_form->setValue($this->aData);
        $this->_form->buttonBack('init');
    }

    /**
     * Построить форму по массиву параметров.
     *
     * @param FormBuilder $oForm - форма
     * @param array $aParams - параметры
     */
    public static function buildFormByParamsArray(FormBuilder $oForm, $aParams)
    {
        foreach ($aParams as $aParam) {
            $sType = 'string';
            $aParams = [];

            switch ($aParam['typeParam']) {
                case 'url':
                    $sType = 'file';
                    break;

                case 'color':
                    $sType = 'colorselector';
                    break;

                case 'color_rgba':
                    $sType = 'colorselector';
                    $aParams = [
                        'saveType' => 'rgba',
                    ];
                    break;

                case 'font-weight':
                    $sType = 'select';
                    $aValues = ['normal', 'bold', 'bolder', 'lighter'];
                    $aParams = [
                        'show_val' => array_combine($aValues, $aValues),
                        'emptyStr' => true,
                    ];
                    break;

                case 'family':
                    $sType = 'select';
                    $aValues = ['Tahoma', 'Arial', 'Verdana', 'Times New Roman'];

                    if (\Yii::$app->register->moduleExists('Fonts', Layer::TOOL)) {
                        $aActiveFonts = fonts\Api::getActiveFontsNameWithDefFamily();
                        $aValues = array_merge($aValues, $aActiveFonts);
                    }

                    $aParams = [
                        'show_val' => array_combine($aValues, $aValues),
                        'emptyStr' => true,
                    ];
                    break;

                case 'font-style':
                    $sType = 'select';
                    $aValues = ['normal', 'italic'];
                    $aParams = [
                        'show_val' => array_combine($aValues, $aValues),
                        'emptyStr' => true,
                    ];
                    break;

                case 'repeat':
                    $sType = 'select';
                    $aValues = ['repeat', 'no-repeat', 'repeat-x', 'repeat-y'];
                    $aParams = [
                        'show_val' => array_combine($aValues, $aValues),
                        'emptyStr' => true,
                    ];
                    break;

                case 'position':
                    $sType = 'select';
                    $aValues = ['left', 'right', 'center', 'top', 'bottom', true];
                    $aParams = [
                        'show_val' => array_combine($aValues, $aValues),
                        'emptyStr' => true,
                    ];
                    break;

                case 'h-position':
                    $sType = 'select';
                    $aValues = ['left', 'right', 'center', true];
                    $aParams = [
                        'show_val' => array_combine($aValues, $aValues),
                        'emptyStr' => true,
                    ];
                    break;

                case 'v-position':
                    $sType = 'select';
                    $aValues = ['top', 'center', 'bottom', true];
                    $aParams = [
                        'show_val' => array_combine($aValues, $aValues),
                        'emptyStr' => true,
                    ];
                    break;

                case 'h-position-abs':
                    $sType = 'select';
                    $aValues = ['left', 'right', true];
                    $aParams = [
                        'show_val' => array_combine($aValues, $aValues),
                        'emptyStr' => true,
                    ];
                    break;

                case 'v-position-abs':
                    $sType = 'select';
                    $aValues = ['top', 'bottom', true];
                    $aParams = [
                        'show_val' => array_combine($aValues, $aValues),
                        'emptyStr' => true,
                    ];
                    break;

                case 'text-transform':
                    $sType = 'select';
                    $aValues = ['none', 'capitalize', 'lowercase', 'uppercase', 'inherit', true];
                    $aParams = [
                        'show_val' => array_combine($aValues, $aValues),
                        'emptyStr' => true,
                    ];
                    break;

                case 'text-align':
                    $sType = 'select';
                    $aValues = ['left', 'right', 'center', 'justify'];
                    $aParams = [
                        'show_val' => array_combine($aValues, $aValues),
                        'emptyStr' => true,
                    ];
                    break;

                case 'vectical':
                case 'vectical-align':
                    $sType = 'select';
                    $aValues = ['baseline', 'sub', 'super', 'top', 'middle', 'bottom', 'text-top', 'text-bottom'];
                    $aParams = [
                        'show_val' => array_combine($aValues, $aValues),
                        'emptyStr' => true,
                    ];
                    break;

                case 'border-style':
                    $sType = 'select';
                    $aValues = ['none', 'hidden', 'dotted', 'dashed', 'solid', 'double', 'groove', 'ridge', 'inset', 'outset', 'inherit'];
                    $aParams = [
                        'show_val' => array_combine($aValues, $aValues),
                        'emptyStr' => true,
                    ];
                    break;

                case 'text-decoration':
                    $sType = 'select';
                    $aValues = ['none', 'underline', 'overline', 'line-through', 'blink'];
                    $aParams = [
                        'show_val' => array_combine($aValues, $aValues),
                        'emptyStr' => true,
                    ];
                    break;

                case 'switch':
                    $sType = 'select';
                    $aValues = ['block', 'none', 'table-cell'];
                    $aParams = [
                        'show_val' => array_combine($aValues, $aValues),
                        'emptyStr' => true,
                    ];
                    break;

                case 'enable-selector':
                    $sType = 'select';
                    $aValues = ['enabled', 'disabled'];
                    $aParams = [
                        'show_val' => array_combine($aValues, $aValues),
                        'emptyStr' => true,
                    ];
                    break;

                case 'bg-attachment':
                    $sType = 'select';
                    $aValues = ['scroll', 'fixed'];
                    $aParams = [
                        'show_val' => array_combine($aValues, $aValues),
                        'emptyStr' => true,
                    ];
                    break;

                case 'width_koef':
                    $sType = 'select';
                    $aValues = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
                    $aParams = [
                        'show_val' => array_combine($aValues, $aValues),
                        'emptyStr' => true,
                    ];
                    break;

                case 'yes/no':
                    $sType = 'select';
                    $aParams = [
                        'show_val' => ['Нет', 'Да'],
                        'emptyStr' => false,
                    ];
                    break;
            }

            $aParams += [
                'groupTitle' => $aParam['groupTitle'],
            ];

            $sParamName = $aParam['groupName'] . ';' . $aParam['paramName'];

            $oForm->field($sParamName, $aParam['paramTitle'], $sType, $aParams);
        }
    }
}
