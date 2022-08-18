<?php

namespace skewer\build\Design\Settings;

use skewer\base\section\Parameters;
use skewer\build\Cms;
use skewer\build\Design\Zones;
use skewer\components\design\Template;

class Module extends Cms\Tabs\ModulePrototype
{
    /**
     * Состояние начальное.
     */
    protected function actionInit()
    {
        $this->render(new view\Index());
    }

    /**
     * Сохранение настроек.
     */
    protected function actionSaveSettings()
    {
        \Yii::$app->session->set('Settings.DeleteParams', $this->getInDataVal('DeleteParams'));

        $this->addMessage('Сохранено');
        $this->actionInit();

        $this->fireJSEvent('reload_all');
    }

    /**
     * Форма настроек.
     */
    protected function actionSettingsForm()
    {
        $this->render(new view\Settings([
            'bDeleteParams' => (bool) \Yii::$app->session->get('Settings.DeleteParams'),
        ]));
    }

    protected function actionFormTplForm()
    {
        $sTpl = Parameters::getShowValByName(
            \Yii::$app->sections->root(),
            Zones\Api::layoutGroupName,
            'form_tpl'
        );

        $this->render(new view\Form([
            'aTplList' => Template::getTplList('form'),
            'sCurrentTpl' => $sTpl,
        ]));
    }

    protected function actionChangeForm()
    {
        $sName = $this->getInDataVal('tpl');
        $sUpdContent = $this->getInDataVal('setContent');

        Template::change('form', $sName, $sUpdContent);

        $this->fireJSEvent('reload_all');

        $this->actionInit();
    }

    public function actionCategoryViewerForm()
    {
        $sWidget = Parameters::getValByName(
            \Yii::$app->sections->tplNew(),
            'CategoryViewer',
            'category_widget'
        );

        $this->render(new view\CategoryViewer([
            'aWidgetList' => Template::getTplList('categoryViewer'),
            'sCurrentWidget' => $sWidget,
        ]));
    }

    public function actionChangeCategoryViewer()
    {
        $sName = $this->getInDataVal('widget');

        Template::change('categoryViewer', $sName, false);

        $this->fireJSEvent('reload_all');

        $this->actionInit();
    }
}
