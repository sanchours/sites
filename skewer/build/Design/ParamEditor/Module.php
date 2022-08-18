<?php

namespace skewer\build\Design\ParamEditor;

use skewer\base\site\Layer;
use skewer\build\Cms;
use skewer\build\Design\Inheritance\Api;
use skewer\components\design;
use skewer\components\design\DesignManager;
use skewer\components\fonts\Api as FontsApi;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

/**
 * Класс для редактирования набора параметров дизайнерского режима
 * Class Module.
 */
class Module extends Cms\Frame\ModulePrototype
{
    /**
     * Состояние. Выбор корневого набора разделов.
     *
     * @return bool
     */
    protected function actionInit()
    {
        // команда инициализации
        $this->setCmd('init');

        $this->addLibClass('ParamEditorGrid');
        $this->addLibClass('ParamEditorGridColumns');
    }

    /**
     * Состояние. Выбор корневого набора разделов.
     *
     * @param null $iGroupId
     *
     * @return bool
     */
    protected function actionLoadItems($iGroupId = null)
    {
        $aSearchText = \Yii::$app->session->getFlash('search_text');

        if (isset($aSearchText[0])) {
            $this->actionFindParam($aSearchText[0]);
        } else {
            // id группы
            if ($iGroupId === null) {
                $iGroupId = $this->getInt('groupId');
            }

            // команда отображения списка
            $this->setCmd('loadItems');

            // запросить данные для вывода
            $items = DesignManager::getParamsByGroup($iGroupId);

            // указываем источник для унаследованных параметров
            foreach ($items as &$item) {
                if ($item['ancestor'] and $item['active'] == 1) {
                    if ($param = design\model\Params::findOne(['name' => $item['ancestor'], 'layer' => 'default'])) {
                        $item['value'] = '[' . $param->value . '] < ' .
                            implode(' - ', array_reverse(Api::getParamTitlePathAsArray($param->group))) .
                            ' - ' . $param->title;
                    } else {
                        $item['value'] = $item['ancestor'];
                    }
                }

                $oInstaller = new \skewer\components\config\installer\Api();

                if ($item['type'] == 'family' && $oInstaller->isInstalled('Fonts', Layer::TOOL)) {
                    $aActiveFonts = FontsApi::getActiveFontsNameWithDefFamily();

                    if ($aActiveFonts) {
                        $item['defvalue'] = $aActiveFonts;
                    }
                }
            }

            $this->setData('canDelete', (bool) \Yii::$app->session->get('Settings.DeleteParams'));
            $this->setData('items', $items);
            $this->setData('groupId', $iGroupId);
        }
    }

    protected function actionRemoveParam()
    {
        $iId = $this->getInt('id');
        DesignManager::deleteById($iId);

        $this->actionLoadItems();

        $this->fireJSEvent('reload_show_frame');
        \Yii::$app->clearAssets();
    }

    /**
     * Обновление параметра.
     */
    protected function actionUpdParam()
    {
        // входной набор эдементов
        $iId = $this->getStr('id');
        $sValue = $this->getStr('value');

        try {
            /*Попробуем вызвать метод типа превалидации если он есть*/
            $aParam = design\model\Params::find()
                ->where(['id' => $iId])
                ->asArray()
                ->one();

            $sMethodName = 'beforeSave' . str_replace('.', '', $aParam['name']);

            if (method_exists('\\' . __NAMESPACE__ . '\Custom', $sMethodName)) {
                $sValue = call_user_func('\\' . __NAMESPACE__ . '\Custom::' . $sMethodName, $sValue);
            }

            // сохранить
            $bRes = DesignManager::saveCSSParamValue($iId, $sValue);
            if ($bRes) {
                $this->fireJSEvent('reload_show_frame');
                \Yii::$app->clearAssets();
            } else {
                $this->addError('Значение не сохранено');
            }

            /*
             * Выполнение после сохранения
             */
//            $sMethodName = 'afterSave' . str_replace('.', '', $aParam['name']);
//
//            if (method_exists('\\' . __NAMESPACE__ . '\Custom', $sMethodName))
//                call_user_func('\\' . __NAMESPACE__ . '\Custom::' . $sMethodName, $this);
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }
    }

    /**
     * Сбрасывает значение параметра на стандартное.
     */
    protected function actionRevertParam()
    {
        // входной набор эдементов
        $iId = $this->getInt('id');

        // откатить
        if (DesignManager::revertCSSParam($iId)) {
            $this->actionLoadItems();
        }

        $this->fireJSEvent('reload_show_frame');
        \Yii::$app->clearAssets();
    }

    /**
     * Сохраняет значение отступов при перетаскивании мышкой блоков в шапке.
     */
    protected function actionSaveCssParams()
    {
        $aData = $this->get('data');

        $sParamPath = trim($aData['paramPath']);

        $oParamH = design\model\Params::findOne(['name' => $sParamPath . '.h_value']);
        if ($oParamH and isset($aData['hValue'])) {
            $oParamH->value = ((int) $aData['hValue']) . 'px';
            $oParamH->save();
        }

        $oParamV = design\model\Params::findOne(['name' => $sParamPath . '.v_value']);
        if ($oParamV and isset($aData['vValue'])) {
            $oParamV->value = ((int) $aData['vValue']) . 'px';
            $oParamV->save();
        }

        $this->fireJSEvent('reload_show_frame');
        \Yii::$app->clearAssets();
    }

    protected function actionActiveLink()
    {
        $id = $this->get('id');
        $active = $this->get('active');
        $sSearchText = $this->get('search_text');

        if ($sSearchText) {
            \Yii::$app->session->addFlash('search_text', $sSearchText);
        }

        DesignManager::setActiveParamRefs($id, $active);

        $this->fireJSEvent('reload_show_frame');
        $this->fireJSEvent('reload_param_editor');
        $this->fireJSEvent('reload_inheritance');
        \Yii::$app->clearAssets();
    }

    /**
     * Вывод набора записей с учетам фильтра.
     *
     * @param $sSearchText
     *
     * @throws Exception
     */
    protected function actionFindParam($sSearchText = null)
    {
        if ($sSearchText !== null) {
            $sText = $sSearchText;
        } else {
            $sText = $this->getStr('text');
        }

        if (mb_strlen($sText) < 3) {
            throw new Exception('Слишком короткий запрос');
        }
        // команда отображения списка
        $this->setCmd('findParam');

        // запросить данные для вывода
        $items = DesignManager::getParamsSearch($sText);

        $aGroups = design\model\Groups::find()
            ->asArray()
            ->all();

        $aGroups = ArrayHelper::map($aGroups, 'id', 'title');

        // указываем источник для унаследованных параметров
        foreach ($items as &$item) {
            if ($item['ancestor'] and $item['active'] == 1) {
                if ($param = design\model\Params::findOne(['name' => $item['ancestor'], 'layer' => 'default'])) {
                    $item['value'] = '[' . $param->value . '] < ' .
                        implode(' - ', array_reverse(Api::getParamTitlePathAsArray($param->group))) .
                        ' - ' . $param->title;
                } else {
                    $item['value'] = $item['ancestor'];
                }
            }

            $aParents = \skewer\build\Design\ParamTree\Api::getAllParents($item['group']);

            $aOutParents = [];
            foreach ($aParents as &$subitem) {
                if (isset($aGroups[$subitem])) {
                    $aOutParents[] = $aGroups[$subitem];
                }
            }

            $item['title'] = implode('>', $aOutParents) . '<br>' . $item['title'];
        }

        $this->setData('canDelete', (bool) \Yii::$app->session->get('Settings.DeleteParams'));

        /*Сообщаем о лимите если записей слишком много*/
        if (count($items) == 50) {
            $this->addMessage('Выводятся первые 50 записей');
        }

        $this->setData('items', $items);
    }
}
