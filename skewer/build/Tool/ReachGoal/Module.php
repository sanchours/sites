<?php

namespace skewer\build\Tool\ReachGoal;

use skewer\base\ui\ARSaveException;
use skewer\build\Tool;
use skewer\components\targets;
use yii\base\UserException;

/**
 * Модуль управления Целями (ReachGoal)
 * Class Module.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    /** имя параметра для счетчика яндекса */
    const yaCounter = 'yaReachGoalCounter';

    /** имя параметра с js кодами для счетчиков */
    const jsCounters = 'countersCode';

    protected function actionInit()
    {
        $aTargets = targets\models\Targets::find()
            ->asArray()
            ->all();

        $aTypes = targets\Creator::getTypes();

        $this->render(new Tool\ReachGoal\view\Index([
            'aTypes' => $aTypes,
            'aTargets' => $aTargets,
        ]));
    }

    protected function actionShow()
    {
        $this->actionShowForm();
    }

    /**
     * Отображение формы для добавления Яндекс ReachGoal.
     *
     * @throws UserException
     */
    protected function actionShowForm()
    {
        $aData = $this->get('data');
        $iItemId = isset($aData['id']) ? (int) $aData['id'] : 0;
        $type = $this->get('type') ?: $this->getInDataVal('type');

        if (!$type) {
            throw new UserException('Not type');
        }

        if ($iItemId) {
            $oTargetRow = targets\models\Targets::findOne(['id' => $iItemId]);
        } else {
            $oTargetRow = targets\models\Targets::getNewRow($aData, $type);
        }

        if (!$oTargetRow) {
            throw new UserException('Item not found');
        }

        $this->render(new view\ShowForm([
            'targetRow' => $oTargetRow,
        ]));
    }

    /**
     * Удаление цели.
     */
    protected function actionDelete()
    {
        // запросить данные
        $aData = $this->get('data');

        $oMatches = new targets\CheckTarget();

        if (!isset($aData['name'])) {
            $aTarget = targets\models\Targets::find()
                ->where(['id' => $aData['id']])
                ->one();
            $aData['name'] = $aTarget['name'];
        }

        $oMatches->sName = $aData['name'];
        \Yii::$app->trigger('target_delete', $oMatches);
        $aMatches = $oMatches->getList();

        if (!empty($aMatches)) {
            throw new UserException(\Yii::t('ReachGoal', 'used_in_') . '<br>' . implode(',<br>', $aMatches));
        }
        // id записи
        $iItemId = (is_array($aData) and isset($aData['id'])) ? (int) $aData['id'] : 0;

        targets\models\Targets::deleteAll(['id' => $iItemId]);

        $this->actionInit();
    }

    /**
     * Сохранение данных.
     */
    protected function actionSave()
    {
        // запросить данные
        $aData = $this->get('data');
        $iId = $this->getInDataValInt('id');

        if (!$aData) {
            throw new UserException('Empty data');
        }
        if ($iId) {
            $oTargetRow = targets\models\Targets::findOne(['id' => $iId]);
            if (!$oTargetRow) {
                throw new UserException("Запись [{$iId}] не найдена");
            }
        } else {
            if (targets\Api::checkDuplicate($aData['name'])) {
                throw new UserException(\Yii::t('ReachGoal', 'target_exists'));
            }
            /** @var targets\types\Prototype $oType */
            $oType = targets\Creator::getObject($aData['type']);

            $oTargetRow = $oType->getNewTargetRow($aData);
        }
        $oTargetRow->setAttributes($aData);

        if (!$oTargetRow->save()) {
            throw new ARSaveException($oTargetRow);
        }

        // вывод списка
        $this->actionInit();
    }

    /**
     * Отображение интерфейса настроек.
     */
    protected function actionSettings()
    {
        $aFields = targets\Creator::getParams();
        $aData = [];

        foreach ($aFields as $field) {
            $aData[$field['name']] = $field['value'];
        }

        $this->render(new Tool\ReachGoal\view\Settings([
            'aFields' => $aFields,
            'aData' => $aData,
        ]));
    }

    /**
     * Сохранение настроек.
     */
    protected function actionSaveSettings()
    {
        $aInputData = $this->getInData();

        targets\Creator::setParams($aInputData);

        $this->actionInit();
    }

    /****************СОСТОЯНИЯ СЕЛЕКТОРОВ********************/

    /**
     * Список селекторов.
     */
    protected function actionShowSelectors()
    {
        $aSelectors = targets\models\TargetSelectors::find()
            ->groupBy(['selector'])
            ->asArray()
            ->all();

        $this->render(new Tool\ReachGoal\view\ShowSelectors([
            'aSelectors' => $aSelectors,
        ]));
    }

    /**
     * Добавление селектора.
     *
     * @throws UserException
     */
    protected function actionAddSelector()
    {
        $this->showEditFormSelector();
    }

    /**
     * Вывод формы.
     *
     * @throws UserException
     */
    private function showEditFormSelector()
    {
        $aData = $this->get('data');
        $sSelector = $aData['selector'] ?? '';
        $bAddForm = isset($aData['selector']);

        // Добавляем новый селектор?
        if ($bAddForm) {
            $aTargetSelectors = targets\models\TargetSelectors::find()
                ->where(['selector' => $sSelector])
                ->asArray()
                ->all();
            $aParams = [];
            $aParams['selector'] = $sSelector;
            // текущее значение селектора
            $aParams['old_selector'] = $sSelector;
            foreach ($aTargetSelectors as $item) {
                if (!isset($aParams[$item['type'] . '_target'])) {
                    $aParams[$item['type'] . '_target'] = $item['name'];
                    $aParams['title'] = $item['title'];
                }
            }
        } else {
            $aParams = [];
        }

        $aTypes = targets\Creator::getTypes();

        $this->render(new Tool\ReachGoal\view\EditFormSelector([
            'aTypes' => $aTypes,
            'aParams' => $aParams,
        ]));
    }

    /**
     * Сохранение селектора.
     *
     * @throws UserException
     */
    protected function actionSaveSelector()
    {
        // запросить данные
        $aData = $this->get('data');

        if (!$aData) {
            throw new UserException('Empty data');
        }
        // Новый селектор?
        $bIsNew = (isset($aData['old_selector']) && !mb_strlen($aData['old_selector']));

        // Проверка на дубликаты
        if ($bIsNew || ($aData['old_selector'] != $aData['selector'])) {
            if (targets\models\TargetSelectors::findOne(['selector' => $aData['selector']])) {
                throw new UserException(\Yii::t('ReachGoal', 'selector_already_exist', [$aData['selector']]));
            }
        }

        if (!$bIsNew) {
            // Удаляем связки цель-селектор по старому названию селектора
            targets\models\TargetSelectors::deleteAll(['selector' => $aData['old_selector']]);
        }

        /*2. Попробуем создать их*/
        foreach ($aData as $key => $item) {
            if (mb_strpos($key, '_target') !== false) {
                $model = new targets\models\TargetSelectors();
                $model->selector = $aData['selector'];
                $model->type = str_replace('_target', '', $key);
                $model->name = $item;
                $model->title = $aData['title'];

                if (!$model->save()) {
                    throw new ARSaveException($model);
                }
            }
        }

        \Yii::$app->clearAssets();
        // вывод списка
        $this->actionShowSelectors();
    }

    /**
     * Удаление селектора.
     */
    protected function actionDeleteSelector()
    {
        // запросить данные
        $aData = $this->get('data');

        // id записи
        $sSelector = (is_array($aData) and isset($aData['selector'])) ? $aData['selector'] : '';

        targets\models\TargetSelectors::deleteAll(['selector' => $sSelector]);

        $this->actionShowSelectors();

        \Yii::$app->clearAssets();
    }

    /**
     * Форма селектора.
     *
     * @throws UserException
     */
    protected function actionShowSelector()
    {
        $this->showEditFormSelector();
    }
}
