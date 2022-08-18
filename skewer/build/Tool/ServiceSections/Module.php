<?php

namespace skewer\build\Tool\ServiceSections;

use skewer\base\section\models\TreeSection;
use skewer\base\section\Parameters;
use skewer\base\ui\ARSaveException;
use skewer\build\Tool;
use skewer\components\i18n\Languages;
use skewer\components\i18n\models\ServiceSections;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Модуль для редактирования системных разделов
 * Class Module.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    protected $lang_filter = false;

    protected function preExecute()
    {
        $this->lang_filter = $this->get('lang_filter', false);
    }

    public function actionInit()
    {
        $this->actionList();
    }

    public function actionList()
    {
        $aLanguages = Languages::getAllActive();

        $oQuery = ServiceSections::find()
            ->asArray()
            ->orderBy(['language' => SORT_ASC]);

        if ($this->lang_filter) {
            $oQuery->where(['language' => $this->lang_filter]);
        }

        $aServiceSections = $oQuery->all();

        $this->render(new Tool\ServiceSections\view\Index([
            'bManyLanguages' => (count($aLanguages) > 1),
            'aLanguages' => ArrayHelper::map($aLanguages, 'name', 'title'),
            'sLangFilter' => $this->lang_filter,
            'aServiceSections' => $aServiceSections,
        ]));
    }

    public function actionAddNewSection()
    {
        $aLanguages = ArrayHelper::map(Languages::getAllActive(), 'name', 'title');
        $this->render(new view\FormSection(['item' => ServiceSections::getNewRow(), 'languages' => $aLanguages]));
    }

    public function actionSave()
    {
        $aData = $this->getInData();

        if (isset($aData['id']) && $aData['id']) {
            /** @var ServiceSections $oSection */
            $oSection = ServiceSections::findOne(['id' => $aData['id']]);

            if ($oSection !== null) {
                $oSection->value = (isset($aData['value'])) ? (int) $aData['value'] : 0;
                $oSection->save();
            }
        } else {
            $bNameExists = ServiceSections::find()
                ->where(['name' => $aData['name'], 'language' => $aData['language']])
                ->exists();

            $bSectionExists = TreeSection::find()
                ->where(['id' => $aData['value']])
                ->exists();

            if ($bNameExists) {
                throw new UserException(\Yii::t('languages', 'error_section_name'));
            }

            if (!$bSectionExists && $aData['value']) {
                throw new UserException(\Yii::t('languages', 'error_section') . $aData['value']);
            }

            $oSection = ServiceSections::getNewRow();
            $oSection->setAttributes($aData);

            if (!$oSection->save()) {
                throw new ARSaveException($oSection);
            }
            //Запрещаем удаление указанного раздела, т.к. он теперь системный
            Parameters::setParams($aData['value'], '.', '_break_delete', 1);
        }

        $this->actionList();
    }
}
