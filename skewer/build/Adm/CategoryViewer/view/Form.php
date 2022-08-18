<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 19.12.2016
 * Time: 15:19.
 */

namespace skewer\build\Adm\CategoryViewer\view;

use skewer\base\section\models\TreeSection;
use skewer\base\section\Visible;
use skewer\build\Adm\CategoryViewer\Seo;
use skewer\components\catalog\Section;
use skewer\components\ext\view\FormView;
use skewer\components\gallery\Profile;

class Form extends FormView
{
    public $bShowIcons;
    public $aData;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldString('category_title', \Yii::t('categoryViewer', 'param_title'), ['groupTitle' => \Yii::t('categoryViewer', 'settings_layout_block')])
            ->fieldCheck('category_parent', \Yii::t('categoryViewer', 'param_parent'), ['groupTitle' => \Yii::t('categoryViewer', 'settings_layout_block')])
            ->fieldInt('category_from', \Yii::t('categoryViewer', 'param_from'), ['minValue' => 0, 'groupTitle' => \Yii::t('categoryViewer', 'settings_layout_block')]);

        $this->buildFieldAdditionalSections();

        $this->_form
            ->fieldCheck('category_show', \Yii::t('categoryViewer', 'param_show'), ['groupTitle' => \Yii::t('categoryViewer', 'settings_view_in_layout')])
            ->fieldCheck('category_use_alt_title', \Yii::t('categoryViewer', 'param_use_alt_title'), ['groupTitle' => \Yii::t('categoryViewer', 'settings_view_in_layout')])
            ->fieldGallery(
                'category_img',
                \Yii::t('categoryViewer', 'param_image'),
                Profile::getDefaultId(Profile::TYPE_CATEGORYVIEWER),
                [
                    'seoClass' => Seo::className(),
                    'groupTitle' => \Yii::t('categoryViewer', 'settings_view_in_layout')
                ]
            )
            ->fieldWysiwyg('category_description', \Yii::t('categoryViewer', 'param_description'), 350, '', ['groupTitle' => \Yii::t('categoryViewer', 'settings_view_in_layout')]);

        if ($this->bShowIcons) {
            $this->_form->field('category_icon', \Yii::t('categoryViewer', 'param_icon'), 'imagefile', ['groupTitle' => \Yii::t('categoryViewer', 'settings_view_in_layout')]);
        }
        $this->_form
            ->setValue($this->aData)
            ->buttonSave()
            ->buttonBack('show')
            ->buttonSeparator()
            ->button('designSettings', \Yii::t('categoryViewer', 'design_settings'), 'icon-configuration')
            ->buttonConfirm('setRecursive', \Yii::t('categoryViewer', 'setRecursive'), \Yii::t('categoryViewer', 'setRecursiveConfirm'), 'icon-edit')
            ->buttonConfirm('unsetRecursive', \Yii::t('categoryViewer', 'unsetRecursive'), \Yii::t('categoryViewer', 'unsetRecursiveConfirm'), 'icon-edit');
    }

    /** Добавит в форму поле мультиселект с выбором разделов */
    public function buildFieldAdditionalSections()
    {
        $aStructure = Section::getListWithStructure();

        $aVisibleSections = TreeSection::find()
            ->where(['visible' => Visible::$aOpenByLink])
            ->indexBy('id')
            ->asArray()
            ->column();

        $aNonVisibleSections = array_keys(array_diff_key($aStructure, $aVisibleSections));
        $aDisabledVariants = array_merge($aNonVisibleSections, [\Yii::$app->sections->leftMenu(), \Yii::$app->sections->topMenu()]);

        //Покрасим элементы, которые не являются каталогом
        foreach ($aStructure as $iId => &$item) {
            if (in_array($iId, $aDisabledVariants)) {
                $item = '<i style="color: #b3b3b3">' . $item . '</i>';
            }
        }

        $this->_form->fieldMultiSelect(
                'category_additional_sections',
                \Yii::t('categoryViewer', 'param_additional_sections'),
                $aStructure,
                [],
                [
                    'groupTitle' => \Yii::t('categoryViewer', 'settings_layout_block'),
                    'disabledVariants' => $aDisabledVariants
                ]
            );
    }
}
