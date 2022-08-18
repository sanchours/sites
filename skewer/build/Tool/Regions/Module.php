<?php

namespace skewer\build\Tool\Regions;

use skewer\base\ui;
use skewer\build\Tool\Labels\models\Labels;
use skewer\build\Tool\LeftList\ModulePrototype;
use skewer\build\Tool\Regions\models\LabelsForRegion;
use skewer\build\Tool\Regions\view\EditValueLabel;
use skewer\build\Tool\Regions\view\Index;
use skewer\build\Tool\Regions\view\Labels as ViewLabels;
use skewer\build\Tool\Regions\view\Settings;
use skewer\build\Tool\Regions\view\Show;
use skewer\components\regions\models\RegionLabels;
use skewer\components\regions\models\Regions;
use skewer\components\regions\ParamForRegion;
use yii\base\UserException;

class Module extends ModulePrototype
{
    /** @var int Id региона */
    public $idRegion = 0;

    protected function actionInit()
    {
        $this->idRegion = 0;

        return $this->render(new Index([
            'regions' => Regions::getAll(),
        ]));
    }

    protected function actionAddRegion()
    {
        return $this->render(new Show([
            'title' => \Yii::t('regions', 'title_add'),
            'region' => Regions::getNewRow(),
            'isCreate' => false,
        ]));
    }

    protected function actionShow()
    {
        if (!$this->idRegion) {
            $this->idRegion = $this->getInDataVal('id');
        }

        return $this->render(new Show([
            'title' => \Yii::t('regions', 'title_edit'),
            'region' => Regions::getById($this->idRegion),
            'isCreate' => true,
        ]));
    }

    /**
     * Сохранение региона.
     *
     * @throws UserException
     */
    protected function actionSave()
    {
        $params = $this->get('data');
        $id = $this->getInDataVal('id', '');

        if (!$params) {
            throw new UserException('Заполните пожалуйста данные');
        }

        $region = Regions::getById($id);
        $region->setAttributes($params);

        if ($region->validate()) {
            $region->save();
        } else {
            throw new ui\ARSaveException($region);
        }

        $this->actionInit();
    }

    /**
     * Удаление региона.
     *
     * @throws \Exception
     */
    protected function actionDelete()
    {
        $id = $this->getInDataVal('id', '');

        // удаление региона
        $region = Regions::findOne($id);
        if ($region) {
            $region->delete();
        }

        // удаление всех значений меток
        RegionLabels::deleteByRegionId($id);

        $this->actionInit();
    }

    /**
     * Список меток для региона.
     */
    protected function actionListLabels()
    {
        $region = Regions::getById($this->idRegion);

        return $this->render(new ViewLabels([
            'title' => \Yii::t('regions', 'title_list') . ": {$region->city}",
            'labels' => LabelsForRegion::getLabels($this->idRegion),
        ]));
    }

    /**
     * Редактирование значения метки.
     *
     * @throws UserException
     */
    protected function actionEditValueLabel()
    {
        $iLabelId = $this->getInDataValInt('id', null);
        $iRegionId = $this->getInDataValInt('regionId', null);

        if ($iLabelId === null || $iRegionId === null) {
            throw new UserException($iLabelId);
        }

        $label = Labels::getById($iLabelId);

        $oRegionLabel = RegionLabels::getByLabelAndRegion($iLabelId, $iRegionId);

        if ($oRegionLabel) {
            $label->default = $oRegionLabel->value;
        }

        return $this->render(
            new EditValueLabel([
                'title' => \Yii::t('regions', 'title_label_edit'),
                'label' => $label,
            ])
        );
    }

    /**
     * Сохранение значения метки для региона.
     *
     * @throws UserException
     */
    protected function actionSaveValueLabel()
    {
        if (!$this->get('data')) {
            throw new UserException('Недостаточно данных для сохранения');
        }

        $valueForSave = $this->getInDataVal('default');
        $idLabel = $this->getInDataVal('id', '');

        $labelMain = Labels::getById($idLabel);
        if ($labelMain->default === $valueForSave) {
            $this->addMessage('Уведомление', 'Значение метки не было изменено.');

            return $this->actionListLabels();
        }

        $valueLabel = RegionLabels::getByLabelAndRegion($idLabel, $this->idRegion);

        if ($valueLabel === null) {
            $valueLabel = RegionLabels::createNewOnLabelRegion($idLabel, $this->idRegion);
        }

        $valueLabel->value = $this->getInDataVal('default');

        if (!$valueLabel->save()) {
            throw new ui\ARSaveException($valueLabel);
        }

        return $this->actionListLabels();
    }

    /**
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    protected function actionDeleteValueLabel()
    {
        $idLabel = $this->getInDataVal('id');
        $valueLabel = RegionLabels::getByLabelAndRegion($idLabel, $this->idRegion);

        if ($valueLabel) {
            $valueLabel->delete();
        }

        $this->actionListLabels();
    }

    /**
     * @throws UserException
     * @throws \yii\db\Exception
     */
    protected function actionSetDefault()
    {
        $idRegion = $this->getInDataVal('id', '');

        $defaultRegion = Regions::setDefaultRegion($idRegion);

        if (!$defaultRegion) {
            throw new UserException(\Yii::t('regions', 'region_error'));
        }

        $this->addMessage(\Yii::t('regions', 'message_set_default'));

        $this->actionShow();
    }

    /**
     * Изменение активности из списквого интерфейса.
     *
     * @throws ui\ARSaveException
     */
    protected function actionChangeActive()
    {
        $idItem = $this->getInDataVal('id', 0);

        $region = Regions::findOne(['id' => $idItem]);

        if ($region) {
            $region->active = $this->getInDataVal('active');

            if ($region->validate() && $region->save()) {
                return $this->actionInit();
            }

            throw new ui\ARSaveException($region);
        }

        return $this->actionInit();
    }

    public function actionSettings()
    {
        $paramRegion = new ParamForRegion();
        $settings = [
            'active' => $paramRegion->hasInstallParam(),
        ];

        return $this->render(new Settings(
            [
                'title' => \Yii::t('regions', 'settings_region_module'),
                'settings' => $settings,
            ]
        ));
    }

    public function actionSaveSettings()
    {
        $active = $this->getInDataVal('active');

        $paramRegion = new ParamForRegion();

        if ($active) {
            $paramRegion->install();
        } else {
            $paramRegion->remove();
        }

        return $this->actionInit();
    }
}
