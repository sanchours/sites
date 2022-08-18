<?php

namespace skewer\build\Adm\CategoryViewer;

use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\base\SysVar;
use skewer\base\ui;
use skewer\build\Adm;
use skewer\build\Adm\CategoryViewer\models\CategoryViewerCssParams;
use skewer\build\Page\CategoryViewer\Api;
use skewer\components\design\DesignManager;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class Module extends Adm\Tree\ModulePrototype
{
    public $category_parent = 0;
    public $category_show = 0;
    public $category_use_alt_title = 0;
    public $category_img = '';
    public $category_from = 0;
    public $category_icon = '';
    public $category_widget = '';
    public $category_title = '';

    /**
     * @var string Описание раздела
     */
    public $category_description = '';
    public $category_additional_sections;

    public function actionInit()
    {
        $this->category_icon = (string) Parameters::getValByName($this->sectionId(), Parameters::settings, 'category_icon');
        $this->category_description = Parameters::getShowValByName($this->sectionId(), $this->getConfigParam('param_group'), 'category_description');

        $this->actionShow();
    }

    protected function actionShow()
    {
        $aData = [
            'category_parent' => $this->category_parent,
            'category_show' => $this->category_show,
            'category_use_alt_title' => $this->category_use_alt_title,
            'category_img' => $this->category_img,
            'category_from' => $this->category_from,
            'category_icon' => $this->category_icon,
            'category_description' => $this->category_description,
            'category_widget' => $this->category_widget,
            'category_additional_sections' => $this->category_additional_sections,
            'category_title' => $this->category_title,
        ];
        $this->render(new view\Form([
            'bShowIcons' => SysVar::get('Menu.ShowIcons'),
            'aData' => $aData,
        ]));
    }

    /**
     * Рекурсивно выставляет всем потомкам вывод категорий.
     */
    protected function actionSetRecursive()
    {
        $iVal = 1;

        Api::toggleShowCategory4SubSections($this->sectionId(), $iVal);

        $this->category_parent = $iVal;

        $this->actionShow();
    }

    /**
     * Рекурсивно отключает всем потомкам вывод категорий.
     */
    protected function actionUnsetRecursive()
    {
        $iVal = 0;

        Api::toggleShowCategory4SubSections($this->sectionId(), $iVal);

        $this->category_parent = $iVal;

        $this->actionShow();
    }

    protected function actionSave()
    {
        $aData = $this->getInData();

        $sGroupName = $this->getConfigParam('param_group');

        // на случай если не найден параметр.
        if (!$sGroupName) {
            throw new UserException('Can not find group name');
        }
        $this->category_parent = ArrayHelper::getValue($aData, 'category_parent', '');
        $this->category_show = ArrayHelper::getValue($aData, 'category_show', '');
        $this->category_use_alt_title = ArrayHelper::getValue($aData, 'category_use_alt_title', '');
        $this->category_img = ArrayHelper::getValue($aData, 'category_img', '');
        $this->category_from = ArrayHelper::getValue($aData, 'category_from', '');
        $this->category_icon = ArrayHelper::getValue($aData, 'category_icon', '');
        $this->category_description = ArrayHelper::getValue($aData, 'category_description', '');
        $this->category_additional_sections = ArrayHelper::getValue($aData, 'category_additional_sections', '');
        $this->category_title = ArrayHelper::getValue($aData, 'category_title', '');

        Parameters::setParams($this->sectionId(), $sGroupName, 'category_parent', $this->category_parent);
        Parameters::setParams($this->sectionId(), $sGroupName, 'category_show', $this->category_show);
        Parameters::setParams($this->sectionId(), $sGroupName, 'category_use_alt_title', $this->category_use_alt_title);
        Parameters::setParams($this->sectionId(), $sGroupName, 'category_img', $this->category_img);
        Parameters::setParams($this->sectionId(), $sGroupName, 'category_from', $this->category_from);
        Parameters::setParams($this->sectionId(), $sGroupName, 'category_description', null, $this->category_description);
        Parameters::setParams($this->sectionId(), $sGroupName, 'category_additional_sections', $this->category_additional_sections);
        Parameters::setParams($this->sectionId(), $sGroupName, 'category_title', $this->category_title);

        if (SysVar::get('Menu.ShowIcons')) {
            Parameters::setParams($this->sectionId(), Parameters::settings, 'category_icon', $this->category_icon);
        }

        $oTree = Tree::getSection($this->sectionId());
        $oTree->last_modified_date = date('Y-m-d H:i:s', time());
        $oTree->save();

        $this->actionShow();
    }

    /** Настройки дизайна */
    public function actionDesignSettings()
    {
        $sTpl = Api::getWidgetBySection($this->sectionId());

        $aListParams = Api::getCssParamsByWidget($sTpl);

        $aCssParamsGroupBySections = Api::getCssParamsBySections($sTpl, $this->sectionId());
        $aCssParamsCurrentSection = $aCssParamsGroupBySections[$this->sectionId()];

        $this->render(new Adm\CategoryViewer\view\DesignSettings([
            'aListParams' => $aListParams,
            'aData' => $aCssParamsCurrentSection,
        ]));
    }

    /** Сохранение настроек дизайна */
    public function actionSaveDesignParameters()
    {
        $aData = $this->get('data');

        $aDefParams = Api::getCssParamsByWidget(Api::getWidgetBySection($this->sectionId()));

        $aDefParams = ArrayHelper::map(
            $aDefParams,
            static function ($item) {
                return $item['groupName'] . ';' . $item['paramName'];
            },
            static function ($item) {
                return $item;
            }
        );

        $bHasChange = false;

        foreach ($aData as $sKey => $sValueParam) {
            list($sGroupName, $sParamName) = explode(';', $sKey);

            $oParam = CategoryViewerCssParams::getExistOrNewParam($this->sectionId(), $sGroupName, $sParamName);

            $sValueParam = (string) $sValueParam;

            $aCurrentDefParam = ArrayHelper::getValue($aDefParams, $oParam->group . ';' . $oParam->paramName);

            if ($sValueParam === '') {
                $sValueParam = ArrayHelper::getValue($aCurrentDefParam, 'defValue', '');
            }

            $oParam->value = DesignManager::handleValueByType($sValueParam, $aCurrentDefParam['typeParam']);

            if ($oParam->getDirtyAttributes()) {
                $bHasChange = true;
            }

            if (!$oParam->save()) {
                throw new ui\ARSaveException($oParam);
            }
        }

        // Были изменения -> чистим ассеты
        if ($bHasChange) {
            \Yii::$app->clearAssets();
        }

        $this->actionInit();
    }
}
